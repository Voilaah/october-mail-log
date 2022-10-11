<?php

namespace Voilaah\MailLog\Updates;

use Illuminate\Support\Facades\Mail;
use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreatevoilaahMaillogLog extends Migration
{
    public function up()
    {
        Schema::create('voilaah_maillog_log', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('to')->nullable();
            $table->string('from')->nullable();
            $table->string('subject')->nullable();
            $table->string('template')->nullable();
            $table->boolean('sent')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('voilaah_maillog_log');
    }
}
