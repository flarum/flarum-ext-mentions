<?php

/*
 * This file is part of Flarum.
 *
 * (c) Toby Zerner <toby.zerner@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        // Make sure the entities exist so that we will be able to create
        // foreign keys without any issues.
        $connection = $schema->getConnection();
        $prefix = $connection->getTablePrefix();
        $connection->statement("delete from {$prefix}posts_mentioned_posts
            where not exists (select 1 from {$prefix}posts where id = post_id)
            or not exists (select 1 from {$prefix}posts where id = mentions_id)");

        $schema->table('posts_mentioned_posts', function (Blueprint $table) {
            $table->renameColumn('mentions_id', 'mentions_post_id');

            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('mentions_post_id')->references('id')->on('posts')->onDelete('cascade');
        });
    },

    'down' => function (Builder $schema) {
        $schema->table('posts_mentioned_posts', function (Blueprint $table) {
            $table->dropForeign(['post_id', 'mentions_post_id']);

            $table->renameColumn('mentions_post_id', 'mentions_id');
        });
    }
];
