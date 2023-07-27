<?php

namespace yii1tech\di\test\console;

use CDummyCache;
use CFormatter;
use ICache;
use Yii;
use yii1tech\di\console\ConsoleCommandRunner;
use yii1tech\di\Container;
use yii1tech\di\DI;
use yii1tech\di\test\support\ConsoleApplication;
use yii1tech\di\test\TestCase;

class ConsoleApplicationTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication(
            [
                'commandPath' => __DIR__ . '/../support/commands'
            ],
            ConsoleApplication::class
        );

        $container = new Container();

        $container->instance(ICache::class, new CDummyCache());
        $container->instance(CFormatter::class, new CFormatter());

        DI::setContainer($container);
    }

    public function testCreateCommand(): void
    {
        $_SERVER['argv'] = ['yiic', 'plain'];

        Yii::app()->run();

        $this->assertTrue(isset($GLOBALS['command']));
        $this->assertTrue(isset($GLOBALS['method']));

        /** @var \PlainCommand $command */
        $command = $GLOBALS['command'];
        $this->assertTrue($command instanceof \PlainCommand);
        $this->assertTrue($command->cache instanceof ICache);
        $this->assertSame('actionIndex', $GLOBALS['method']);
        $this->assertSame('plain', $command->name);
        $this->assertTrue($command->getCommandRunner() instanceof ConsoleCommandRunner);
    }

    /**
     * @depends testCreateCommand
     */
    public function testRunActionWithParams(): void
    {
        $id = 123;
        $_SERVER['argv'] = ['yiic', 'plain', 'format', "--id={$id}"];

        Yii::app()->run();

        $this->assertTrue(isset($GLOBALS['command']));

        /** @var \PlainCommand $command */
        $command = $GLOBALS['command'];
        $this->assertTrue($command instanceof \PlainCommand);

        $this->assertTrue($command->formatter instanceof CFormatter);

        $this->assertTrue(isset($GLOBALS['id']));
        $this->assertEquals($id, $GLOBALS['id']);
    }

    /**
     * @depends testCreateCommand
     */
    public function testRunActionCatchingAllArgs(): void
    {
        $id = 123;
        $_SERVER['argv'] = ['yiic', 'plain', 'all', $id];

        Yii::app()->run();

        $this->assertTrue(isset($GLOBALS['command']));

        /** @var \PlainCommand $command */
        $command = $GLOBALS['command'];
        $this->assertTrue($command instanceof \PlainCommand);

        $this->assertTrue(isset($GLOBALS['args']));
        $this->assertEquals([$id], $GLOBALS['args']);
    }

    /**
     * @depends testCreateCommand
     */
    public function testGetHelp(): void
    {
        /** @var \PlainCommand $command */
        $command = DI::make(\PlainCommand::class, [
            'name' => 'plain',
            'runner' => new ConsoleCommandRunner(),
        ]);

        $help = $command->getHelp();

        $this->assertStringContainsString('index', $help);
        $this->assertStringContainsString('format --id=value', $help);
        $this->assertStringNotContainsString('formatter', $help);
    }
}