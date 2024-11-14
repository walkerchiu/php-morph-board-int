<?php

namespace WalkerChiu\MorphBoard;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use WalkerChiu\Core\Models\Constants\CountryZone;
use WalkerChiu\MorphBoard\Models\Constants\BoardType;
use WalkerChiu\MorphBoard\Models\Entities\Board;
use WalkerChiu\MorphBoard\Models\Entities\BoardLang;
use WalkerChiu\MorphBoard\Models\Repositories\BoardRepository;

class BoardRepositoryTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected $repository;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        //$this->loadLaravelMigrations(['--database' => 'mysql']);
        $this->loadMigrationsFrom(__DIR__ .'/../migrations');
        $this->withFactories(__DIR__ .'/../../src/database/factories');

        $this->repository = $this->app->make(BoardRepository::class);
    }

    /**
     * To load your package service provider, override the getPackageProviders.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return Array
     */
    protected function getPackageProviders($app)
    {
        return [\WalkerChiu\Core\CoreServiceProvider::class,
                \WalkerChiu\MorphBoard\MorphBoardServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
    }

    /**
     * A basic functional test on BoardRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\Repository
     *
     * @return void
     */
    public function testMorphBoardRepository()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-morph-board.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-morph-board.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-morph-board.soft_delete', 1);

        $faker = \Faker\Factory::create();

        $user_id = 1;
        DB::table(config('wk-core.table.user'))->insert([
            'id'       => $user_id,
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password
        ]);

        // Give
        for ($i=1; $i<=3; $i++)
            $this->repository->save([
                'type'           => $faker->randomElement(config('wk-core.class.morph-board.boardType')::getCodes()),
                'user_id'        => $user_id,
                'serial'         => $faker->isbn10,
                'identifier'     => $faker->slug,
                'is_highlighted' => $faker->boolean,
                'is_enabled'     => $faker->boolean
            ]);

        // Get and Count records after creation
            // When
            $records = $this->repository->get();
            $count   = $this->repository->count();
            // Then
            $this->assertCount(3, $records);
            $this->assertEquals(3, $count);

        // Find someone
            // When
            $record = $this->repository->find(1);
            // Then
            $this->assertNotNull($record);

            // When
            $record = $this->repository->find(4);
            // Then
            $this->assertNull($record);

        // Delete someone
            // When
            $this->repository->deleteByIds([1]);
            $count = $this->repository->count();
            // Then
            $this->assertEquals(2, $count);

            // When
            $this->repository->deleteByExceptIds([3]);
            $count = $this->repository->count();
            $record = $this->repository->find(3);
            // Then
            $this->assertEquals(1, $count);
            $this->assertNotNull($record);

            // When
            $count = $this->repository->where('id', '>', 0)->count();
            // Then
            $this->assertEquals(1, $count);

            // When
            $count = $this->repository->whereWithTrashed('id', '>', 0)->count();
            // Then
            $this->assertEquals(3, $count);

            // When
            $count = $this->repository->whereOnlyTrashed('id', '>', 0)->count();
            // Then
            $this->assertEquals(2, $count);

        // Force delete someone
            // When
            $this->repository->forcedeleteByIds([3]);
            $records = $this->repository->get();
            // Then
            $this->assertCount(0, $records);

        // Restore records
            // When
            $this->repository->restoreByIds([1, 2]);
            $count = $this->repository->count();
            // Then
            $this->assertEquals(2, $count);
    }

    /**
     * Unit test about Lang creation on BoardRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\RepositoryTrait
     *     WalkerChiu\MorphBoard\Models\Repositories\MorphBoardRepository
     * 
     * @return void
     */
    public function testcreateLangWithoutCheck()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-morph-board.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-morph-board.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-morph-board.soft_delete', 1);

        $faker = \Faker\Factory::create();

        $user_id = 1;
        DB::table(config('wk-core.table.user'))->insert([
            'id'       => $user_id,
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password
        ]);

        // Give
        factory(Board::class)->create(['user_id' => $user_id]);

        // Find record
            // When
            $record = $this->repository->find(1);
            // Then
            $this->assertNotNull($record);

        // Create Lang
            // When
            $lang = $this->repository->createLangWithoutCheck(['morph_type' => get_class($record), 'morph_id' => $record->id, 'code' => 'en_us', 'key' => 'name', 'value' => 'Hello']);
            // Then
            $this->assertInstanceOf(BoardLang::class, $lang);
    }

    /**
     * Unit test about Query List on BoardRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\RepositoryTrait
     *     WalkerChiu\MorphBoard\Models\Repositories\MorphBoardRepository
     *
     * @return void
     */
    public function testQueryList()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-morph-board.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-morph-board.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-morph-board.soft_delete', 1);

        $faker = \Faker\Factory::create();

        $user_id = 1;
        DB::table(config('wk-core.table.user'))->insert([
            'id'       => $user_id,
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password
        ]);

        // Give
        factory(Board::class, 4)->create(['user_id' => $user_id]);

        // Get query
            // When
            sleep(1);
            $this->repository->find(3)->touch();
            $records = $this->repository->ofNormal(null, null)->get();
            // Then
            $this->assertCount(4, $records);

            // When
            $record = $records->first();
            // Then
            $this->assertArrayNotHasKey('deleted_at', $record->toArray());
            $this->assertEquals(3, $record->id);

        // Get query of trashed records
            // When
            $this->repository->deleteByIds([4]);
            $this->repository->deleteByIds([1]);
            $records = $this->repository->ofTrash(null, null)->get();
            // Then
            $this->assertCount(2, $records);

            // When
            $record = $records->first();
            // Then
            $this->assertArrayHasKey('deleted_at', $record);
            $this->assertEquals(1, $record->id);
    }

    /**
     * Unit test about FormTrait on BoardRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\RepositoryTrait
     *     WalkerChiu\MorphBoard\Models\Repositories\MorphBoardRepository
     *     WalkerChiu\Core\Models\Forms\FormTrait
     *
     * @return void
     */
    public function testFormTrait()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-morph-board.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-morph-board.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-morph-board.soft_delete', 1);

        $faker = \Faker\Factory::create();

        $user_id = 1;
        DB::table(config('wk-core.table.user'))->insert([
            'id'       => $user_id,
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password
        ]);

        // Name
            // Give
            $db_morph_1 = factory(Board::class)->create(['user_id' => $user_id]);
            $db_morph_2 = factory(Board::class)->create(['user_id' => $user_id]);
            $this->repository->createLangWithoutCheck(['morph_id' => 1, 'morph_type' => Board::class, 'code' => 'en_us', 'key' => 'name', 'value' => 'Hello']);
            $this->repository->createLangWithoutCheck(['morph_id' => 2, 'morph_type' => Board::class, 'code' => 'zh_tw', 'key' => 'name', 'value' => '您好']);
            // When
            $result_1 = $this->repository->checkExistName('en_us', null, 'Hello');
            $result_2 = $this->repository->checkExistName('en_us', null, 'Hi');
            $result_3 = $this->repository->checkExistName('en_us', 1, 'Hello');
            $result_4 = $this->repository->checkExistName('en_us', 1, '您好');
            $result_5 = $this->repository->checkExistName('zh_tw', 1, '您好');
            // Then
            $this->assertTrue($result_1);
            $this->assertTrue(!$result_2);
            $this->assertTrue(!$result_3);
            $this->assertTrue(!$result_4);
            $this->assertTrue($result_5);
    }
}
