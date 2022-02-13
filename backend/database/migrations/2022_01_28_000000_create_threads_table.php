<?php

use Database\Seeders\DefaultRoleTableSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Allows `Polymorphic Relationships` while still relying on
        // `cascadeOnDelete`. For more info see:
        // https://laracasts.com/discuss/channels/eloquent/polymorphism-why-should-i-violate-database-design?page=1&replyId=748370
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
        });
        // Track user actions (such as: creating posts or comments and liking or
        // disliking things).
        //
        // This basically adds an optional `created_by_user_id` to the `entries`
        // table.
        Schema::create('user_actions', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // An entry can only be connected to a single user so mark the
            // `entry_id` as `unique`.
            $table->foreignId('entry_id')->unique()->constrained('entries')->cascadeOnDelete();
        });


        // Set it up so that a user can have multiple roles. For more info see:
        // https://stackoverflow.com/questions/37093523/laravel-how-to-check-if-user-is-admin
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();

            // Enforce that there are no duplicates (can't really have a role twice):
            $table->unique(['user_id', 'role_id']);
        });


        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->text('scrambled_body');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // $table->binary('image')->nullable();
            $table->string('image_mime_type')->nullable();
            $table->timestamps();
        });
        DB::statement("ALTER TABLE posts ADD image MEDIUMBLOB");

        Schema::create('post_comments', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->text('scrambled_content');
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Track likes and dislikes.
        //
        // `is_like` column was inspired by:
        // https://stackoverflow.com/questions/14202168/sql-database-structure-for-like-and-dislike
        //
        // Likes can be represented in different ways (currently we are using
        // the last option via the `entries` table):
        //
        // - Using `Polymorphic Relationships` where one table has ids that
        //   could for one of multiple other tables.
        //   - More info at:
        //     https://laravel.com/docs/9.x/eloquent-relationships#many-to-many-polymorphic-relations
        //   - This is also the suggested solution by this blog post:
        //     https://dev.to/bdelespierre/how-to-implement-a-simple-like-system-with-laravel-lfe
        //   - Con: the database cannot constrain the foreign keys since they
        //     can be to multiple different tables. This makes it harder to
        //     ensure likeable things such as post and comments are not
        //     referenced after they have been deleted.
        //     - This discussion has some interesting views about situations
        //       where you are not using foreign keys:
        //       https://news.ycombinator.com/item?id=21486494
        //     - One way to fix this is to be very careful with locks.
        //       - For more info about how to use locks in Laravel see:
        //         https://laravel.com/docs/8.x/queries#pessimistic-locking
        //       - If each post is locked with a shared lock while adding a like
        //         and locked with a update/write lock when removing the post
        //         then that should be enough to ensure there are no likes to
        //         posts that have been deleted.
        //     - Another way is to double check that a likeable thing exists
        //       after adding a like to it and if it doesn't then remove that
        //       like. (This doesn't work without problems as currently
        //       suggested.)
        //       - Example of adding like:
        //         1. Start transaction
        //         2. Add like
        //         3. Check if likeable exists.
        //           - Intuitively it seems like that if the post still exists
        //             then at least the "removing post" code hasn't reached
        //             step 2 yet and will therefore remove our like later.
        //             - But since transactions are only visible after they
        //               complete we probably would not be able to see the
        //               removal of the likeable until after the removal
        //               operation has finished completely. Meaning that the
        //               above code is not correct unless we remove the likeable
        //               without a transaction. But that means that there would
        //               still be likes to the likeable (post) even after it was
        //               deleted. And if the program exits or crashes then
        //               nothing will preform that final cleanup that deletes
        //               all the likes.
        //               - For more info about why the database behaves like
        //                 this see:
        //                 https://www.sqlshack.com/concurrency-problems-theory-and-experimentation-in-sql-server/
        //         4. If not then remove like.
        //         5. End transaction.
        //       - Example of removing post:
        //         1. Start transaction
        //         2. Remove post
        //         3. Remove all likes that references this post.
        //         4. End transaction.
        // - Using a separate table for each sub-type that then has the id of a
        //   "super" table entry.
        //   - So instead of one `likeable` table we would have `post_likes` and
        //     `comment_likes` as well as a `likes` table whose ids are
        //     referenced in the sub-tables.
        //     - So essentially the keys to `posts` or `comments` aren't stored
        //       directly in the `likes` table but in another table that then
        //       has a reference to the `likes` table.
        //     - In our case we actually use a `user_actions` table instead of a
        //       `likes` table since we want to not just get references to likes
        //       but also other user actions such as creating posts or comments.
        //   - This pattern is argued for in this article:
        //     http://duhallowgreygeek.com/polymorphic-association-bad-sql-smell/
        //   - Optionally we could also store the id of the sub-table's
        //     (`comment_like`) entry in the super table (`likes`) to easier get
        //     sub table entries. This has the disadvantage that this ids could
        //     be corrupted since they can't be constrained by the database but
        //     if we rarely change them it could be fine.
        //     - Enforcing the validity of these optional ids can be done with
        //       some quite advanced SQL statements:
        //       https://stackoverflow.com/questions/28222533/polymorphism-for-foreign-key-constraints
        //   - When removing one shouldn't delete entries in the sub-tables
        //     (`post_likes`) instead one should delete their entry in the
        //     super-table (`likes`) and rely on cascading deletes to remove the
        //     entries from the sub-tables.
        //   - When removing a likeable thing (such as a post) then the
        //     sub-tables should have keys to that thing which enforces their
        //     validity and those keys SHOULD NOT be marked as "cascade on
        //     delete". This ensures that the post cannot be deleted while there
        //     are still remaining likes to it.
        //     - So sub-tables would have a `like_id` (cascade delete) and a
        //       `post_id` (enforce but don't cascade) column.
        //       - If all likes for a post is deleted then the sub-tables that
        //         have references to the post will all be deleted as well.
        //     - We need to use locks to ensure that deleting a likeable thing
        //       (post) does not fail because of a newly added like.
        //       - For more info about how to use locks in Laravel see:
        //         https://laravel.com/docs/8.x/queries#pessimistic-locking
        //       - Taking a shared lock of the likeable thing (post) when adding
        //         a like and taking a update/write lock when deleting the
        //         likeable thing (post) should be enough to ensure the delete
        //         operation never fails.
        // - Just hardcode a different table for each likeable. Don't keep any
        //   table with relations to multiple other tables.
        //   - So each "like" table has a "user_id" and a "<entry>_id" where
        //     "<entry>" can be "post" or "comment" depending on what table we
        //     are defining.
        //   - If we want to access something similar to a `likes` table we can
        //     just join all our different like tables together with a lot of
        //     join statements.
        //     - This has the drawback that it is hard (impossible?) to sort
        //       based on different columns. So can't sort based on `created_at`
        //       column for different tables.
        //       - So can't make a page listing all likes a user has made.
        // - Have a "likeable" table in which a row is created whenever a
        //   likeable thing (such as a post) is created. When the likeable thing
        //   is removed then that row should also be removed.
        //   - So Likeable morphs into a Post or Comment.
        //   - A Like references a Likeable (and a User).
        //   - Ensure a "likeable" table row is deleted whenever the thing it
        //     represents (a post or comment) is deleted.
        //     - One way to do this is to never remove a Post or Comment
        //       directly and instead remove their Likeable entry. If each Post
        //       and Comment has a foreign key to its likeable row then the
        //       database will cascade the delete and also remove the Post or
        //       Comment.
        //     - Another way is to just use transactions to remove both likeable
        //       things (such as posts or comments) and their "likeable" table
        //       row at the same time.
        //   - This should allow for creating likes and removing posts without
        //     using any locks or other tricks.
        // - Instead of having a single id that could be a key in multiple
        //   tables we could have a table which has many nullable foreign ids of
        //   which only one isn't null.
        //   - This approach is suggested at:
        //     https://laracasts.com/discuss/channels/eloquent/polymorphism-why-should-i-violate-database-design?page=1&replyId=748370
        //
        // The pros and cons of these approaches are also discussed at:
        // https://www.reddit.com/r/laravel/comments/i78u4x/how_to_choose_between_pivot_and_polymorphism/
        //
        // Take note of this comment which mentions disadvantages of the
        // `Polymorphic Relationships` approach:
        // https://www.reddit.com/r/laravel/comments/i78u4x/comment/g1azdn8/?utm_source=reddit&utm_medium=web2x&context=3
        //
        //
        // General info about race conditions and locks for databases:
        //
        // - For more info about SQL locks see:
        //   https://www.sqlshack.com/locking-sql-server/
        // - MySQL documentation for how shared locks and update locks work:
        //   https://dev.mysql.com/doc/refman/5.7/en/innodb-locking-reads.html
        // - For more info about how to use locks in Laravel see:
        //   https://laravel.com/docs/8.x/queries#pessimistic-locking
        // - Common database concurrency issues:
        //   https://www.sqlshack.com/concurrency-problems-theory-and-experimentation-in-sql-server/
        // - Optimistic locking:
        //   https://stackoverflow.com/questions/17431338/optimistic-locking-in-mysql/18806907#18806907
        // - Different locks supported by MySQL:
        //   https://dev.mysql.com/doc/refman/5.7/en/internal-locking.html
        // - Article about duplicated rows due to race condition (also suggest a
        //   couple of solutions):
        //   https://freek.dev/1087-breaking-laravels-firstorcreate-using-race-conditions
        //   - Suggests using database unique "index" to make the database
        //     enforce no duplicates.
        //     - More info about "unique" indexes for many columns:
        //       - https://stackoverflow.com/questions/20065697/schema-builder-laravel-migrations-unique-on-two-columns
        //       - https://stackoverflow.com/questions/39375309/how-to-make-combination-of-two-columns-as-a-unique-key
        //       - https://stackoverflow.com/questions/16990723/laravel-4-making-a-combination-of-values-columns-unique
        //       - https://laravel.com/docs/8.x/migrations#creating-indexes
        //     - "Upserts" are only allowed if we have a `unique` index:
        //       - https://laravel.com/docs/9.x/queries#upserts

        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            // If this is `false` then the user disliked the post. This should
            // ensure that a user can't both like and dislike a post.
            $table->boolean('is_like');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // The entry that the user likes:
            $table->foreignId('likeable_id')->constrained('entries')->cascadeOnDelete();

            // Enforce that there are no duplicates (can't like a post or
            // comment twice):
            $table->unique(['user_id', 'likeable_id']);
        });


        // Add foreign ids for all created tables to the `entries` table.
        //
        // Note: must do this after those tables have been created.
        Schema::table('entries', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained('roles')->cascadeOnDelete();
            $table->foreignId('post_id')->nullable()->constrained('posts')->cascadeOnDelete();
            $table->foreignId('post_comment_id')->nullable()->constrained('post_comments')->cascadeOnDelete();
            $table->foreignId('like_id')->nullable()->constrained('likes')->cascadeOnDelete();
        });

        // Store some extra info in the User model. If the added columns don't
        // have default values and so requires that an initial value is set then
        // you will also need to modify:
        // - The `app\Models\User.php` model file's `fillable` array to allow
        //   easily setting the attribute.
        // - The `app\Actions\Fortify\CreateNewUser.php` file to ensure
        //   registering new users work.
        // - The `database\factories\UserFactory.php` to ensure database seeding
        //   works.
        //
        // For more info see:
        // https://dev.to/arifiqbal/add-new-field-to-user-profile-in-laravel-8-49ck#step-2-update-database-schema
        Schema::table('users', function (Blueprint $table) {
            // Could use a simple integer:
            $table->integer('content_scrambler_algorithm')->after('email_verified_at');

            // Could use an enum:
            // $table->enum('content_scrambler_algorithm', \App\Models\User::SCRAMBLE_ALGORITHMS)->after('email_verified_at');
        });



        // Automatically run required seeders as part of the migration.
        //
        // For more info see:
        // https://stackoverflow.com/questions/32551662/laravel-5-1-users-roles-and-actions

        // Add roles to database:
        (new DefaultRoleTableSeeder())->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Metadata for other tables:
        Schema::dropIfExists('entries');
        Schema::dropIfExists('user_actions');

        // Authorization roles:
        Schema::dropIfExists('roles');
        Schema::dropIfExists('user_roles');

        // Post and comments:
        Schema::dropIfExists('posts');
        Schema::dropIfExists('post_comments');

        // Likes
        Schema::dropIfExists('likes');

        // Extra user specific data:
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('content_scrambler_algorithm');
        });
    }
}
