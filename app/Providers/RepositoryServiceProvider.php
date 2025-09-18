<?php

namespace App\Providers;

use App\Repositories\Click\ClickRepository;
use App\Repositories\Click\IClickRepository;
use App\Repositories\Domain\DomainRepository;
use App\Repositories\Domain\IDomainRepository;
use App\Repositories\Shortlink\IShortlinkRepository;
use App\Repositories\Shortlink\ShortlinkRepository;
use App\Repositories\User\IUserRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All repository bindings for dependency injection
     *
     * @var array
     */
    protected $repositories = [
        IDomainRepository::class => DomainRepository::class,
        IClickRepository::class => ClickRepository::class,
        IShortlinkRepository::class => ShortlinkRepository::class,
        IUserRepository::class => UserRepository::class,
    ];

    /**
     * Register repository bindings.
     */
    public function register(): void
    {
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}