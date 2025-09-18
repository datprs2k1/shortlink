<?php

namespace App\Http\Controllers;

use App\Services\User\UserService;
use Illuminate\Http\Request;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Exception;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        try {
            $filters = $this->getCommonFilters($request);
            $filters['role'] = $request->get('role', '');
            
            $users = $this->getFilteredUsers($filters);
            
            return view('users.index', [
                'title' => 'Manage Users',
                'users' => $users,
                'filters' => $filters
            ]);
        } catch (Exception $e) {
            return $this->handleException($e, 'loading users');
        }
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('users.create', [
            'title' => 'Create New User'
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = $this->userService->create($validated);
            
            return $this->redirectWithSuccess('users.index', 'User created successfully!');
        } catch (Exception $e) {
            return $this->handleException($e, 'creating user');
        }
    }

    /**
     * Display the specified user.
     */
    public function show(int $id)
    {
        try {
            $user = $this->userService->findById($id);
            
            if (!$user) {
                return $this->notFoundResponse('User');
            }
            
            return view('users.show', [
                'title' => 'User Details',
                'user' => $user
            ]);
        } catch (Exception $e) {
            return $this->handleException($e, 'loading user');
        }
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(int $id)
    {
        try {
            $user = $this->userService->findById($id);
            
            if (!$user) {
                return $this->notFoundResponse('User');
            }
            
            return view('users.edit', [
                'title' => 'Edit User',
                'user' => $user
            ]);
        } catch (Exception $e) {
            return $this->handleException($e, 'loading edit form');
        }
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, int $id)
    {
        try {
            $validated = $request->validated();
            $success = $this->userService->update($validated, $id);
            
            if (!$success) {
                return $this->errorResponse('Failed to update user.');
            }
            
            return $this->redirectWithSuccess('users.index', 'User updated successfully!');
        } catch (Exception $e) {
            return $this->handleException($e, 'updating user');
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(int $id)
    {
        try {
            $success = $this->userService->delete($id);
            
            if (!$success) {
                return $this->errorResponse('Failed to delete user.');
            }
            
            return $this->redirectWithSuccess('users.index', 'User deleted successfully!');
        } catch (Exception $e) {
            return $this->handleException($e, 'deleting user');
        }
    }

    /**
     * Get filtered users
     */
    private function getFilteredUsers(array $filters)
    {
        // This would need filtering methods in the UserService
        // For now, return all users
        return $this->userService->getAll();
    }
}