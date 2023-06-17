<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGidToAllTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('admins')) {
            Schema::table('admins', static function (Blueprint $table) {
                if (!Schema::hasColumn('admins', 'gid')) {
                    $table->string('gid', 55)->nullable()->after('id');
                }
            });
        }
        if (Schema::hasTable('associations')) {
            Schema::table('associations', static function (Blueprint $table) {
                if (!Schema::hasColumn('associations', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('clubs')) {
            Schema::table('clubs', static function (Blueprint $table) {
                if (!Schema::hasColumn('clubs', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('fixtures')) {
            Schema::table('fixtures', static function (Blueprint $table) {
                if (!Schema::hasColumn('fixtures', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('news')) {
            Schema::table('news', static function (Blueprint $table) {
                if (!Schema::hasColumn('news', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('officials')) {
            Schema::table('officials', static function (Blueprint $table) {
                if (!Schema::hasColumn('officials', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('players')) {
            Schema::table('players', static function (Blueprint $table) {
                if (!Schema::hasColumn('players', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('seasons')) {
            Schema::table('seasons', static function (Blueprint $table) {
                if (!Schema::hasColumn('seasons', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('sponsors')) {
            Schema::table('sponsors', static function (Blueprint $table) {
                if (!Schema::hasColumn('sponsors', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('sports')) {
            Schema::table('sports', static function (Blueprint $table) {
                if (!Schema::hasColumn('sports', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('stadiums')) {
            Schema::table('stadiums', static function (Blueprint $table) {
                if (!Schema::hasColumn('stadiums', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('staff')) {
            Schema::table('staff', static function (Blueprint $table) {
                if (!Schema::hasColumn('staff', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('stages')) {
            Schema::table('stages', static function (Blueprint $table) {
                if (!Schema::hasColumn('stages', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('teams')) {
            Schema::table('teams', static function (Blueprint $table) {
                if (!Schema::hasColumn('teams', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', static function (Blueprint $table) {
                if (!Schema::hasColumn('tenants', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('tournaments')) {
            Schema::table('tournaments', static function (Blueprint $table) {
                if (!Schema::hasColumn('tournaments', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('weather')) {
            Schema::table('weather', static function (Blueprint $table) {
                if (!Schema::hasColumn('weather', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('match_statistics')) {
            Schema::table('match_statistics', static function (Blueprint $table) {
                if (!Schema::hasColumn('match_statistics', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('season_templates')) {
            Schema::table('season_templates', static function (Blueprint $table) {
                if (!Schema::hasColumn('season_templates', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
        if (Schema::hasTable('points_table_data')) {
            Schema::table('points_table_data', static function (Blueprint $table) {
                if (!Schema::hasColumn('points_table_data', 'gid')) {
                    $table->string('gid', 55)->after('id')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('admins')) {
            Schema::table('admins', static function (Blueprint $table) {
                if (Schema::hasColumn('admins', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('associations')) {
            Schema::table('associations', static function (Blueprint $table) {
                if (Schema::hasColumn('associations', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('clubs')) {
            Schema::table('clubs', static function (Blueprint $table) {
                if (Schema::hasColumn('clubs', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('fixtures')) {
            Schema::table('fixtures', static function (Blueprint $table) {
                if (Schema::hasColumn('fixtures', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('news')) {
            Schema::table('news', static function (Blueprint $table) {
                if (Schema::hasColumn('news', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('officials')) {
            Schema::table('officials', static function (Blueprint $table) {
                if (Schema::hasColumn('officials', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('players')) {
            Schema::table('players', static function (Blueprint $table) {
                if (Schema::hasColumn('players', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('seasons')) {
            Schema::table('seasons', static function (Blueprint $table) {
                if (Schema::hasColumn('seasons', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('sponsors')) {
            Schema::table('sponsors', static function (Blueprint $table) {
                if (Schema::hasColumn('sponsors', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('sports')) {
            Schema::table('sports', static function (Blueprint $table) {
                if (Schema::hasColumn('sports', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('stadiums')) {
            Schema::table('stadiums', static function (Blueprint $table) {
                if (Schema::hasColumn('stadiums', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('staff')) {
            Schema::table('staff', static function (Blueprint $table) {
                if (Schema::hasColumn('staff', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('stages')) {
            Schema::table('stages', static function (Blueprint $table) {
                if (Schema::hasColumn('stages', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('teams')) {
            Schema::table('teams', static function (Blueprint $table) {
                if (Schema::hasColumn('teams', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', static function (Blueprint $table) {
                if (Schema::hasColumn('tenants', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('tournaments')) {
            Schema::table('tournaments', static function (Blueprint $table) {
                if (Schema::hasColumn('tournaments', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('weather')) {
            Schema::table('weather', static function (Blueprint $table) {
                if (Schema::hasColumn('weather', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('match_statistics')) {
            Schema::table('match_statistics', static function (Blueprint $table) {
                if (Schema::hasColumn('match_statistics', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('season_templates')) {
            Schema::table('season_templates', static function (Blueprint $table) {
                if (Schema::hasColumn('season_templates', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
        if (Schema::hasTable('points_table_data')) {
            Schema::table('points_table_data', static function (Blueprint $table) {
                if (Schema::hasColumn('points_table_data', 'gid')) {
                    $table->dropColumn('gid');
                }
            });
        }
    }
}
