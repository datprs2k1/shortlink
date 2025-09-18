<?php

namespace App\Http\Controllers;

use App\Services\Domain\DomainService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DomainController extends Controller
{
    public function __construct(
        private DomainService $domainService
    ) {}

    /**
     * Display a listing of domains
     */
    public function index(Request $request)
    {
        $domains = $this->domainService->getDomainsWithShortlinkCounts();
        $stats = $this->domainService->getDomainStats();

        return view('domains.index', [
            'title' => 'Domains',
            'domains' => $domains,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new domain
     */
    public function create()
    {
        return view('domains.create', [
            'title' => 'Add New Domain',
            'breadcrumbs' => [
                ['title' => 'Domains', 'url' => route('domains.index')],
                ['title' => 'Add New Domain'],
            ],
        ]);
    }

    /**
     * Store a newly created domain
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Domain name is required.',
            'name.regex' => 'Please enter a valid domain name (e.g., example.com)',
        ]);

        try {
            $domain = $this->domainService->createDomain([
                'name' => $request->name,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return redirect()
                ->route('domains.show', $domain)
                ->with('success', 'Domain created successfully.');
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to create domain: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified domain
     */
    public function show(Request $request, int $id)
    {
        try {
            $domain = $this->domainService->findById($id);

            if (!$domain) {
                return redirect()
                    ->route('domains.index')
                    ->with('error', 'Domain not found.');
            }

            // Get domain statistics
            $stats = $this->domainService->getDomainStats($id);

            // Get shortlinks for this domain
            $shortlinks = $this->domainService->mainRepository
                ->getQuery()
                ->find($id)
                ->shortlinks()
                ->withCount('clicks')
                ->latest()
                ->paginate(10);

            return view('domains.show', [
                'title' => 'Domain: ' . $domain->name,
                'breadcrumbs' => [
                    ['title' => 'Domains', 'url' => route('domains.index')],
                    ['title' => $domain->name],
                ],
                'domain' => $domain,
                'stats' => $stats,
                'shortlinks' => $shortlinks,
            ]);
        } catch (\Exception $e) {
            return redirect()
                ->route('domains.index')
                ->with('error', 'Error loading domain: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified domain
     */
    public function edit(int $id)
    {
        try {
            $domain = $this->domainService->mainRepository->findById($id);

            if (!$domain) {
                return redirect()
                    ->route('domains.index')
                    ->with('error', 'Domain not found.');
            }

            return view('domains.edit', [
                'title' => 'Edit Domain',
                'breadcrumbs' => [
                    ['title' => 'Domains', 'url' => route('domains.index')],
                    ['title' => $domain->name, 'url' => route('domains.show', $domain)],
                    ['title' => 'Edit'],
                ],
                'domain' => $domain,
            ]);
        } catch (\Exception $e) {
            return redirect()
                ->route('domains.index')
                ->with('error', 'Error loading domain: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified domain
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Domain name is required.',
            'name.regex' => 'Please enter a valid domain name (e.g., example.com)',
        ]);

        try {
            $updated = $this->domainService->updateDomain($id, [
                'name' => $request->name,
                'is_active' => $request->boolean('is_active'),
            ]);

            if ($updated) {
                return redirect()
                    ->route('domains.show', $id)
                    ->with('success', 'Domain updated successfully.');
            }

            return back()
                ->with('error', 'Failed to update domain.')
                ->withInput();
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to update domain: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified domain
     */
    public function destroy(int $id)
    {
        try {
            $deleted = $this->domainService->deleteDomainWithShortlinks($id);

            if ($deleted) {
                return redirect()
                    ->route('domains.index')
                    ->with('success', 'Domain and associated shortlinks deleted successfully.');
            }

            return back()
                ->with('error', 'Failed to delete domain.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to delete domain: ' . $e->getMessage());
        }
    }

    /**
     * Toggle domain status
     */
    public function toggleStatus(int $id)
    {
        try {
            $updated = $this->domainService->toggleStatus($id);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Domain status updated successfully.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update domain status.'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
