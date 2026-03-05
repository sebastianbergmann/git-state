<?php declare(strict_types=1);
/*
 * This file is part of sebastian/git-state.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\GitState;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Builder::class)]
#[CoversClass(GitCommandRunner::class)]
#[UsesClass(State::class)]
#[Small]
final class BuilderTest extends TestCase
{
    private const string ORIGIN_INFO = "* remote origin\n  Fetch URL: https://github.com/example/repo.git\n  Push  URL: https://github.com/example/repo.git\n  HEAD branch: (unknown)";
    private const string ORIGIN_INFO_SSH = "* remote origin\n  Fetch URL: git@github.com:example/repo.git\n  Push  URL: git@github.com:example/repo.git\n  HEAD branch: (unknown)";
    private const string BRANCH = 'main';
    private const string COMMIT = 'abc1234def5678abc1234def5678abc1234def56';

    public function testDefaultRunnerIsUsedWhenNoneIsProvided(): void
    {
        $this->assertInstanceOf(Builder::class, new Builder());
    }

    public function testReturnsFalseWhenFirstCommandFails(): void
    {
        $stub = $this->createStub(ShellCommandRunner::class);

        $stub
            ->method('run')
            ->willReturn(false);

        $builder = new Builder(new GitCommandRunner($stub));

        $this->assertFalse($builder->build());
    }

    public function testReturnsFalseWhenNoOriginRemoteExists(): void
    {
        $stub = $this->createStub(ShellCommandRunner::class);

        $stub
            ->method('run')
            ->willReturn('upstream');

        $builder = new Builder(new GitCommandRunner($stub));

        $this->assertFalse($builder->build());
    }

    public function testReturnsFalseWhenSecondCommandFails(): void
    {
        $stub = $this->createStub(ShellCommandRunner::class);

        $stub
            ->method('run')
            ->willReturn('origin', false);

        $builder = new Builder(new GitCommandRunner($stub));

        $this->assertFalse($builder->build());
    }

    public function testReturnsFalseWhenFetchUrlLineIsMissing(): void
    {
        $stub = $this->createStub(ShellCommandRunner::class);

        $stub
            ->method('run')
            ->willReturn('origin', '* remote origin');

        $builder = new Builder(new GitCommandRunner($stub));

        $this->assertFalse($builder->build());
    }

    public function testReturnsFalseWhenBranchCommandFails(): void
    {
        $stub = $this->createStub(ShellCommandRunner::class);

        $stub
            ->method('run')
            ->willReturn('origin', self::ORIGIN_INFO, false);

        $builder = new Builder(new GitCommandRunner($stub));

        $this->assertFalse($builder->build());
    }

    public function testReturnsFalseWhenCommitCommandFails(): void
    {
        $stub = $this->createStub(ShellCommandRunner::class);

        $stub
            ->method('run')
            ->willReturn('origin', self::ORIGIN_INFO, self::BRANCH, false);

        $builder = new Builder(new GitCommandRunner($stub));

        $this->assertFalse($builder->build());
    }

    public function testReturnsFalseWhenStatusCommandFails(): void
    {
        $stub = $this->createStub(ShellCommandRunner::class);

        $stub
            ->method('run')
            ->willReturn('origin', self::ORIGIN_INFO, self::BRANCH, self::COMMIT, false);

        $builder = new Builder(new GitCommandRunner($stub));

        $this->assertFalse($builder->build());
    }

    public function testReturnsStateForCleanCheckout(): void
    {
        $stub = $this->createStub(ShellCommandRunner::class);

        $stub
            ->method('run')
            ->willReturn('origin', self::ORIGIN_INFO, self::BRANCH, self::COMMIT, '');

        $builder = new Builder(new GitCommandRunner($stub));
        $result  = $builder->build();

        $this->assertInstanceOf(State::class, $result);
        $this->assertTrue($result->isClean());
    }

    public function testReturnsStateForDirtyCheckout(): void
    {
        $stub = $this->createStub(ShellCommandRunner::class);

        $stub
            ->method('run')
            ->willReturn('origin', self::ORIGIN_INFO, self::BRANCH, self::COMMIT, ' M src/State.php');

        $builder = new Builder(new GitCommandRunner($stub));
        $result  = $builder->build();

        $this->assertInstanceOf(State::class, $result);
        $this->assertFalse($result->isClean());
    }

    public function testStripsCredentialsFromSshUrl(): void
    {
        $stub = $this->createStub(ShellCommandRunner::class);

        $stub
            ->method('run')
            ->willReturn('origin', self::ORIGIN_INFO_SSH, self::BRANCH, self::COMMIT, ' M src/State.php');

        $builder = new Builder(new GitCommandRunner($stub));
        $result  = $builder->build();

        $this->assertInstanceOf(State::class, $result);
        $this->assertSame('github.com:example/repo.git', $result->originUrl());
    }
}
