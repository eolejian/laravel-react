<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateLoveLikesTable.
 */
class CreateReactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('reacter');
            $table->morphs('reactable');
            $table->string('type')->nullable();
            $table->timestamps();
            $table->unique([
                'reacter_id',
                'reacter_type',
                'reactable_id',
                'reactable_type',
                'type',
            ], 'react_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reactions');
    }
}
