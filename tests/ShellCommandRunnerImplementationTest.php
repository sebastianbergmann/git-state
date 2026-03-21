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

#[CoversClass(ShellCommandRunnerImplementation::class)]
#[Small]
final class ShellCommandRunnerImplementationTest extends TestCase
{
    public function testReturnsOutputOfSuccessfulCommand(): void
    {
        $runner = new ShellCommandRunnerImplementation;

        $result = $runner->run('echo "hello world"');

        $this->assertSame('hello world', $result);
    }

    public function testReturnsFalseForCommandThatExitsWithNonZeroCode(): void
    {
        $runner = new ShellCommandRunnerImplementation;

        $result = $runner->run('exit 1');

        $this->assertFalse($result);
    }

    public function testReturnsFalseForInvalidCommand(): void
    {
        $runner = new ShellCommandRunnerImplementation;

        $result = $runner->run('command_that_does_not_exist_abc123 2>/dev/null');

        $this->assertFalse($result);
    }

    public function testTrimsWhitespaceFromOutput(): void
    {
        $runner = new ShellCommandRunnerImplementation;

        $result = $runner->run('echo "  trimmed  "');

        $this->assertSame('trimmed', $result);
    }

    public function testReturnsEmptyStringForCommandWithNoOutput(): void
    {
        $runner = new ShellCommandRunnerImplementation;

        $result = $runner->run('true');

        $this->assertSame('', $result);
    }

    public function testCapturesStdoutNotStderr(): void
    {
        $runner = new ShellCommandRunnerImplementation;

        $result = $runner->run('echo "stdout" && echo "stderr" >&2');

        $this->assertSame('stdout', $result);
    }

    public function testHandlesMultilineOutput(): void
    {
        $runner = new ShellCommandRunnerImplementation;

        $result = $runner->run('printf "line1\nline2\nline3"');

        $this->assertSame("line1\nline2\nline3", $result);
    }
}
