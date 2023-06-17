<?php

namespace App\Providers;

use App\Association;
use App\Booking;
use App\Fixture;
use App\MatchStatistic;
use App\Observers\AssociationObserver;
use App\Observers\BookingObserver;
use App\Observers\FixtureObserver;
use App\Observers\MatchStatisticObserver;
use App\Observers\OfficialObserver;
use App\Observers\PlayerObserver;
use App\Observers\PointsTableObserver;
use App\Observers\SeasonObserver;
use App\Observers\SportObserver;
use App\Observers\StadiumObserver;
use App\Observers\StaffObserver;
use App\Observers\TenantObserver;
use App\Observers\ClubObserver;
use App\Observers\TeamObserver;
use App\Observers\TournamentObserver;
use App\Observers\UserObserver;
use App\Observers\WeatherObserver;
use App\Official;
use App\Player;
use App\PointsTable;
use App\Season;
use App\Sport;
use App\Stadium;
use App\Staff;
use App\Tenant;
use App\Club;
use App\Team;
use App\Tournament;
use App\User;
use App\Weather;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Tenant::observe(TenantObserver::class);
        User::observe(UserObserver::class);
        Tournament::observe(TournamentObserver::class);
        Season::observe(SeasonObserver::class);
        Fixture::observe(FixtureObserver::class);
        Sport::observe(SportObserver::class);
        Association::observe(AssociationObserver::class);
        Stadium::observe(StadiumObserver::class);
        Club::observe(ClubObserver::class);
        Team::observe(TeamObserver::class);
        Player::observe(PlayerObserver::class);
        Staff::observe(StaffObserver::class);
        Official::observe(OfficialObserver::class);
        Weather::observe(WeatherObserver::class);
        PointsTable::observe(PointsTableObserver::class);
        MatchStatistic::observe(MatchStatisticObserver::class);
        Booking::observe(BookingObserver::class);
    }
}
