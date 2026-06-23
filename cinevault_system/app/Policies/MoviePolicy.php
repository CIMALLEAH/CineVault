<?php
 
namespace App\Policies;
 
use App\Models\Movie;
use App\Models\User;
 
class MoviePolicy
{
    public function viewAny(User $user): bool  { return true; }
    public function view(User $user, Movie $movie): bool { return true; }
 
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'staff']);
    }
 
    public function update(User $user, Movie $movie): bool
    {
        return $user->isAdmin();
    }
 
    /** Admin can delete directly; staff can only request delete */
    public function delete(User $user, Movie $movie): bool
    {
        return $user->isAdmin() && !$movie->hasActiveRental();
    }
 
    public function requestDelete(User $user, Movie $movie): bool
    {
        return $user->isStaff() && !$movie->hasActiveRental();
    }
}