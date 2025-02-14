<?php

namespace Voilaah\MailLog\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class AddBodyAttachmentsCCFieldsToLogTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('voilaah_maillog_log')) {
            Schema::table('voilaah_maillog_log', function (Blueprint $table) {
                $table->string('cc')->nullable();
                $table->string('bcc')->nullable();
                $table->longText('body')->nullable();
                $table->longText('attachments')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('voilaah_maillog_log')) {
            Schema::table('voilaah_maillog_log', function (Blueprint $table) {
                $table->dropColumn('cc');
                $table->dropColumn('bcc');
                $table->dropColumn('body');
                $table->dropColumn('attachments');
            });
        }
    }
}
