<?php

namespace App\Http\Controllers;

use App\Services\Shortlink\ShortlinkService;
use App\Services\Domain\DomainService;
use App\Services\Click\ClickService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Exception;

class RedirectController extends Controller
{
    private ShortlinkService $shortlinkService;
    private DomainService $domainService;
    private ClickService $clickService;

    public function __construct(
        ShortlinkService $shortlinkService,
        DomainService $domainService,
        ClickService $clickService
    ) {
        $this->shortlinkService = $shortlinkService;
        $this->domainService = $domainService;
        $this->clickService = $clickService;
    }

    /**
     * Handle direct shortcode redirect (for primary domain)
     */
    public function directRedirect(Request $request, string $shortCode)
    {
        try {
            $currentDomain = $request->getHost();
            $domain = $this->domainService->findByName($currentDomain);
            
            if (!$domain || !$domain->is_active) {
                return $this->handleNotFound($request, $currentDomain, $shortCode, 'Domain not found or inactive');
            }

            return $this->performRedirect($request, $shortCode, $domain->id);
        } catch (Exception $e) {
            return $this->handleError($e, $request->getHost(), $shortCode);
        }
    }

    /**
     * Handle redirect for custom domains
     */
    public function redirect(Request $request, string $domain, string $shortCode)
    {
        try {
            $domainModel = $this->domainService->findByName($domain);
            
            if (!$domainModel || !$domainModel->is_active) {
                return $this->handleNotFound($request, $domain, $shortCode, 'Domain not found or inactive');
            }

            return $this->performRedirect($request, $shortCode, $domainModel->id);
        } catch (Exception $e) {
            return $this->handleError($e, $domain, $shortCode);
        }
    }

    /**
     * Show password form for direct shortcode
     */
    public function showPasswordFormDirect(Request $request, string $shortCode)
    {
        try {
            $currentDomain = $request->getHost();
            $domain = $this->domainService->findByName($currentDomain);
            
            if (!$domain) {
                return $this->handleNotFound($request, $currentDomain, $shortCode, 'Domain not found');
            }

            $shortlink = $this->shortlinkService->findByShortCode($shortCode, $domain->id);
            
            if (!$shortlink) {
                return $this->handleNotFound($request, $currentDomain, $shortCode, 'Shortlink not found');
            }

            return view('redirect.password', [
                'shortCode' => $shortCode,
                'domain' => null // For direct redirect
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, $request->getHost(), $shortCode);
        }
    }

    /**
     * Verify password for direct shortcode
     */
    public function verifyPasswordDirect(Request $request, string $shortCode)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        try {
            $currentDomain = $request->getHost();
            $domain = $this->domainService->findByName($currentDomain);
            
            if (!$domain) {
                return $this->errorResponse('Domain not found.');
            }

            return $this->verifyPasswordAndRedirect($request, $shortCode, $domain->id);
        } catch (Exception $e) {
            return $this->handleException($e, 'verifying password');
        }
    }

    /**
     * Show password form for custom domain
     */
    public function showPasswordForm(Request $request, string $domainName, string $shortCode)
    {
        try {
            $domain = $this->domainService->findByName($domainName);
            
            if (!$domain) {
                return $this->handleNotFound($request, $domainName, $shortCode, 'Domain not found');
            }

            $shortlink = $this->shortlinkService->findByShortCode($shortCode, $domain->id);
            
            if (!$shortlink) {
                return $this->handleNotFound($request, $domainName, $shortCode, 'Shortlink not found');
            }

            return view('redirect.password', [
                'shortCode' => $shortCode,
                'domain' => $domainName
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, $domainName, $shortCode);
        }
    }

    /**
     * Verify password for custom domain
     */
    public function verifyPassword(Request $request, string $domainName, string $shortCode)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        try {
            $domain = $this->domainService->findByName($domainName);
            
            if (!$domain) {
                return $this->errorResponse('Domain not found.');
            }

            return $this->verifyPasswordAndRedirect($request, $shortCode, $domain->id);
        } catch (Exception $e) {
            return $this->handleException($e, 'verifying password');
        }
    }

    /**
     * Show preview for shortlink
     */
    public function preview(Request $request, string $domainName, string $shortCode)
    {
        try {
            $domain = $this->domainService->findByName($domainName);
            
            if (!$domain) {
                return $this->handleNotFound($request, $domainName, $shortCode, 'Domain not found');
            }

            $shortlink = $this->shortlinkService->findByShortCode($shortCode, $domain->id);
            
            if (!$shortlink) {
                return $this->handleNotFound($request, $domainName, $shortCode, 'Shortlink not found');
            }

            // Don't track clicks for preview
            return view('redirect.preview', [
                'shortlink' => $shortlink,
                'domain' => $domain
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, $domainName, $shortCode);
        }
    }

    /**
     * Perform the actual redirect
     */
    private function performRedirect(Request $request, string $shortCode, int $domainId)
    {
        try {
            // Resolve the shortlink
            $resolution = $this->shortlinkService->resolveShortlink($shortCode, $domainId);
            $shortlink = $resolution['shortlink'];
            $originalUrl = $resolution['original_url'];

            // Check if password is required
            if ($shortlink->password_hash) {
                // Store shortlink ID in session for password verification
                session(['pending_shortlink_id' => $shortlink->id]);
                
                if ($request->getHost() === $shortlink->domain->name) {
                    return redirect()->route('direct.redirect.password', $shortCode);
                } else {
                    return redirect()->route('redirect.password', [$shortlink->domain->name, $shortCode]);
                }
            }

            // Track the click
            $this->trackClick($request, $shortlink);

            // Perform redirect
            return redirect($originalUrl, 302);
            
        } catch (Exception $e) {
            throw $e; // Re-throw to be handled by caller
        }
    }

    /**
     * Verify password and redirect
     */
    private function verifyPasswordAndRedirect(Request $request, string $shortCode, int $domainId)
    {
        try {
            $shortlink = $this->shortlinkService->findByShortCode($shortCode, $domainId);
            
            if (!$shortlink) {
                return $this->errorResponse('Shortlink not found.');
            }

            $password = $request->input('password');
            
            if (!Hash::check($password, $shortlink->password_hash)) {
                return $this->errorResponse('Incorrect password.');
            }

            // Check if shortlink is active and not expired
            if (!$shortlink->is_active) {
                return $this->errorResponse('This shortlink is inactive.');
            }

            if ($shortlink->is_expired) {
                return $this->errorResponse('This shortlink has expired.');
            }

            // Track the click
            $this->trackClick($request, $shortlink);

            // Clear pending shortlink from session
            session()->forget('pending_shortlink_id');

            // Perform redirect
            return redirect($shortlink->original_url, 302);
            
        } catch (Exception $e) {
            return $this->handleException($e, 'processing redirect');
        }
    }

    private function trackClick(Request $request, $shortlink)
    {
        try {
            $clickData = [
                'shortlink_id' => $shortlink->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'country_code' => $this->getCountry($request->ip()),
            ];

            $this->clickService->recordShortlinkClick($shortlink->id, $clickData);
        } catch (Exception $e) {
            // Log error but don't fail the redirect
            logger()->error('Failed to track click', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle not found scenarios
     */
    private function handleNotFound(Request $request, string $domain, string $shortCode, string $reason)
    {
        logger()->warning('Redirect not found', [
            'domain' => $domain,
            'short_code' => $shortCode,
            'reason' => $reason,
            'ip' => $request->ip()
        ]);

        return view('redirect.not-found', [
            'domain' => $domain,
            'shortCode' => $shortCode
        ], 404);
    }

    /**
     * Handle errors
     */
    private function handleError(Exception $e, string $domain, string $shortCode)
    {
        logger()->error('Redirect error', [
            'domain' => $domain,
            'short_code' => $shortCode,
            'error' => $e->getMessage()
        ]);

        return view('redirect.error', [
            'domain' => $domain,
            'shortCode' => $shortCode,
            'message' => 'An error occurred while processing your request.'
        ], 500);
    }

    /**
     * Get country from IP (placeholder)
     */
    private function getCountry(string $ip): ?string
    {
        // Implement IP geolocation logic
        return null;
    }

    /**
     * Get city from IP (placeholder)
     */
    private function getCity(string $ip): ?string
    {
        // Implement IP geolocation logic
        return null;
    }

    /**
     * Get browser from user agent (placeholder)
     */
    private function getBrowser(string $userAgent): ?string
    {
        // Implement user agent parsing logic
        return null;
    }

    /**
     * Get OS from user agent (placeholder)
     */
    private function getOS(string $userAgent): ?string
    {
        // Implement user agent parsing logic
        return null;
    }

    /**
     * Get device type from user agent (placeholder)
     */
    private function getDevice(string $userAgent): ?string
    {
        // Implement user agent parsing logic
        return null;
    }
}