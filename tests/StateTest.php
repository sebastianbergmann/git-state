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
use PHPUnit\Framework\TestCase;

#[CoversClass(State::class)]
#[Small]
final class StateTest extends TestCase
{
    private const string ORIGIN_URL = 'github.com:sebastianbergmann/git-state.git';
    private const string BRANCH     = 'main';
    private const string COMMIT     = 'abc1234def5678abc1234def5678abc1234def56';

    public function testHasOriginUrl(): void
    {
        $state = new State(self::ORIGIN_URL, self::BRANCH, self::COMMIT, true, '');

        $this->assertSame(self::ORIGIN_URL, $state->originUrl());
    }

    public function testHasBranch(): void
    {
        $state = new State(self::ORIGIN_URL, self::BRANCH, self::COMMIT, true, '');

        $this->assertSame(self::BRANCH, $state->branch());
    }

    public function testHasCommit(): void
    {
        $state = new State(self::ORIGIN_URL, self::BRANCH, self::COMMIT, true, '');

        $this->assertSame(self::COMMIT, $state->commit());
    }

    public function testIsConsideredCleanWhenStatusIsEmpty(): void
    {
        $state = new State(self::ORIGIN_URL, self::BRANCH, self::COMMIT, true, '');

        $this->assertTrue($state->isClean());
    }

    public function testIsNotConsideredCleanWhenStatusIsNotEmpty(): void
    {
        $state = new State(self::ORIGIN_URL, self::BRANCH, self::COMMIT, false, ' M src/State.php');

        $this->assertFalse($state->isClean());
    }

    public function testHasStatus(): void
    {
        $status = ' M src/State.php';
        $state  = new State(self::ORIGIN_URL, self::BRANCH, self::COMMIT, false, $status);

        $this->assertSame($status, $state->status());
    }
}
