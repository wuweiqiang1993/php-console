<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-03-08
 * Time: 9:35
 */

namespace inhere\console\utils;

/**
 * Class AnsiCode
 * @package inhere\console\utils
 */
final class AnsiCode
{
    const BEGIN_CHAR = "\033[";

    const END_CHAR = "\033[0m";

    // Control cursor code name list. more @see [[self::$ctrlCursorCodes]]
    const CURSOR_HIDE = 'hide';
    const CURSOR_SHOW = 'show';
    const CURSOR_SAVE_POSITION = 'savePosition';
    const CURSOR_RESTORE_POSITION = 'restorePosition';
    const CURSOR_UP = 'up';
    const CURSOR_DOWN = 'down';
    const CURSOR_FORWARD = 'forward';
    const CURSOR_BACKWARD = 'backward';
    const CURSOR_NEXT_LINE = 'nextLine';
    const CURSOR_PREV_LINE = 'prevLine';
    const CURSOR_COORDINATE = 'coordinate';

    // Control screen code name list. more @see [[self::$ctrlScreenCodes]]
    const CLEAR = 'clear';
    const CLEAR_BEFORE_CURSOR = 'clearBeforeCursor';
    const CLEAR_LINE = 'clearLine';
    const CLEAR_LINE_BEFORE_CURSOR = 'clearLineBeforeCursor';
    const CLEAR_LINE_AFTER_CURSOR = 'clearLineAfterCursor';
    const SCROLL_UP = 'scrollUp';
    const SCROLL_DOWN = 'scrollDown';

    /**
     * current class's instance
     * @var self
     */
    private static $instance;

    /**
     * Control cursor code list
     * @var array
     */
    private static $ctrlCursorCodes = [
        // Hides the cursor. Use [show] to bring it back.
        'hide' => '?25l',

        // Will show a cursor again when it has been hidden by [hide]
        'show' => '?25h',

        // Saves the current cursor position, Position can then be restored with [restorePosition].
        'savePosition' => 's',

        // Restores the cursor position saved with [savePosition]
        'restorePosition' => 'u',

        // Moves the terminal cursor up
        'up' => '%dA',

        // Moves the terminal cursor down
        'down' => '%B',

        // Moves the terminal cursor forward
        'forward' => '%dC',

        // Moves the terminal cursor backward
        'backward' => '%dD',

        // Moves the terminal cursor to the beginning of the next line
        'nextLine' => '%dE',

        // Moves the terminal cursor to the beginning of the previous line
        'prevLine' => '%dF',

        // Moves the cursor to an absolute position given as column and row
        // $column 1-based column number, 1 is the left edge of the screen.
        //  $row 1-based row number, 1 is the top edge of the screen. if not set, will move cursor only in current line.
        'coordinate' => '%dG|%d;%dH' // only column: '%dG', column and row: '%d;%dH'.
    ];

    /**
     * Control screen code list
     * @var array
     */
    private static $ctrlScreenCodes = [
        // Clears entire screen content
        'clear' => '2J', // "\033[2J"

        // Clears text from cursor to the beginning of the screen
        'clearBeforeCursor' => '1J',

        // Clears the line
        'clearLine' => '2K',

        // Clears text from cursor position to the beginning of the line
        'clearLineBeforeCursor' => '1K',

        // Clears text from cursor position to the end of the line
        'clearLineAfterCursor' => '0K',

        // Scrolls whole page up. e.g "\033[2S" scroll up 2 line.
        'scrollUp' => '%dS',

        // Scrolls whole page down.e.g "\033[2T" scroll down 2 line.
        'scrollDown' => '%dT',
    ];

    public static function make()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * build ansi code string
     *
     * ```
     * AnsiCode::build(null, 'u');  // "\033[s" Saves the current cursor position
     * AnsiCode::build(0);          // "\033[0m" Build end char, Resets any ANSI format
     * ```
     *
     * @param mixed $format
     * @param string $type
     * @return string
     */
    public static function build($format, $type = 'm')
    {
        $format = null === $format ? '' : implode(';', (array)$format);

        return "\033[" . implode(';', (array)$format) . $type;
    }

    /**
     * control cursor
     * @param string $typeName
     * @param int $arg1
     * @param null $arg2
     * @return $this
     */
    public function cursor($typeName, $arg1 = 1, $arg2 = null)
    {
        if (!isset(self::$ctrlCursorCodes[$typeName])) {
            Show::error("The [$typeName] is not supported cursor control.", __LINE__);
        }

        $code = self::$ctrlCursorCodes[$typeName];

        // allow argument
        if (false !== strpos($code, '%')) {
            // The special code: ` 'coordinate' => '%dG|%d;%dH' `
            if ($typeName === self::CURSOR_COORDINATE) {
                $codes = explode('|', $code);

                if (null === $arg2) {
                    $code = sprintf($codes[0], $arg1);
                } else {
                    $code = sprintf($codes[1], $arg1, $arg2);
                }

            } else {
                $code = sprintf($code, $arg1);
            }
        }

        echo self::build($code, '');

        return $this;
    }

    /**
     * control screen
     * @param $typeName
     * @param null $arg
     * @return $this
     */
    public function screen($typeName, $arg = null)
    {
        if (!isset(self::$ctrlScreenCodes[$typeName])) {
            Show::error("The [$typeName] is not supported cursor control.", __LINE__);
        }

        $code = self::$ctrlScreenCodes[$typeName];

        // allow argument
        if (false !== strpos($code, '%')) {
            $code = sprintf($code, $arg);
        }

        echo self::build($code, '');

        return $this;
    }

    public function reset()
    {
        echo self::END_CHAR;
    }

    /**
     * @return array
     */
    public static function supportedCursorCtrl()
    {
        return array_keys(self::$ctrlCursorCodes);
    }

    /**
     * @return array
     */
    public static function supportedScreenCtrl()
    {
        return array_keys(self::$ctrlScreenCodes);
    }
}
