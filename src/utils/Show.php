<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-03-10
 * Time: 11:59
 */

namespace inhere\console\utils;

use inhere\console\Helper;
use inhere\console\color\Color;

/**
 * Class Show
 *  show formatted message text
 *
 * @package inhere\console\utils
 */
class Show
{
    const CHAR_SPACE = ' ';
    const CHAR_HYPHEN = '-';
    const CHAR_UNDERLINE = '_';
    const CHAR_EQUAL = '=';
    const CHAR_STAR  = '*';

    const POS_LEFT    = 'l';
    const POS_MIDDLE  = 'm';
    const POS_RIGHT   = 'r';

    /**
     * @var array
     */
    public static $defaultBlocks = [
        'block', 'primary', 'info', 'notice', 'success', 'warning', 'danger', 'error'
    ];

/////////////////////////////////////////////////////////////////
/// Output block Message
/////////////////////////////////////////////////////////////////

    /**
     * @param mixed         $messages
     * @param string|null   $type
     * @param string        $style
     * @param int|boolean   $quit  If is int, setting it is exit code.
     */
    public static function block($messages, $type = 'MESSAGE', $style='default', $quit = false)
    {
        $messages = is_array($messages) ? array_values($messages) : array($messages);

        // add type
        if (null !== $type) {
            $messages[0] = sprintf('[%s] %s', strtoupper($type), $messages[0]);
        }

        $text = implode(PHP_EOL, $messages);
        $color = static::getColor();

        if (is_string($style) && $color->hasStyle($style)) {
            $text = sprintf("<%s>%s</%s>", $style, $text, $style);
        }

        // $this->write($text);
        self::write($text, true, $quit);
    }
    public static function primary($messages, $quit = false)
    {
        static::block($messages, 'IMPORTANT', 'primary', $quit);
    }
    public static function success($messages, $quit = false)
    {
        static::block($messages, 'SUCCESS', 'success', $quit);
    }
    public static function info($messages, $quit = false)
    {
        static::block($messages, 'INFO', 'info', $quit);
    }
    public static function notice($messages, $quit = false)
    {
        static::block($messages, 'NOTICE', 'comment', $quit);
    }
    public static function warning($messages, $quit = false)
    {
        static::block($messages, 'WARNING', 'warning', $quit);
    }
    public static function danger($messages, $quit = false)
    {
        static::block($messages, 'DANGER', 'danger', $quit);
    }
    public static function error($messages, $quit = false)
    {
        static::block($messages, 'ERROR', 'error', $quit);
    }

/////////////////////////////////////////////////////////////////
/// Output Format Message(section/list/helpPanel/panel/table)
/////////////////////////////////////////////////////////////////

    /**
     * @param string $title The title text
     * @param int    $width The title section width
     * @param string $char
     */
    public static function title($title, $width = 80, $char = self::CHAR_EQUAL)
    {
        $title = ucwords(trim($title));
        $msgLength = mb_strlen($title, 'UTF-8');
        $width = is_int($width) && $width > 10 ? $width : 80;

        $indentSpace = str_pad(' ', ceil($width/2) - ceil($msgLength/2), ' ');
        $charStr = str_pad($char, $width, $char);

        self::write("  {$indentSpace}{$title}   \n  {$charStr}\n");
    }

    /**
     * @param string       $title The title text
     * @param string|array $body  The section body message
     * @param array $opts
     */
    public static function section($title, $body, array $opts = [])
    {
        $opts = array_merge([
            'width' => 80,
            'char'  => self::CHAR_HYPHEN,
            'pos'   => self::POS_LEFT,
            'topBorder'    => true,
            'bottomBorder' => true,
        ], $opts);

        // list($sW, $sH) = Helper::getScreenSize();
        $width = (int)$opts['width'];
        $char = trim($opts['char']);

        $title = ucwords(trim($title));
        $tLength = Helper::strLen($title);
        $width = $width > 10 ? $width : 80;
        $indentSpace = '';

        // title position
        if ($tLength >= $width) {
            $indentSpace = '';
        } elseif ($opts['pos'] === self::POS_RIGHT) {
            $indentSpace = str_pad(self::CHAR_SPACE, ceil($width - $tLength), self::CHAR_SPACE);
        } elseif ($opts['pos'] === self::POS_MIDDLE) {
            $indentSpace = str_pad(self::CHAR_SPACE, ceil(($width - $tLength)/2), self::CHAR_SPACE);
        }

        $tpl = "  %s\n%s%s\n%s";// title topBorder body bottomBorder
        $topBorder = $bottomBorder = '';
        $titleLine = "$indentSpace<bold>$title</bold>";

        if ( $opts['topBorder'] || $opts['bottomBorder']) {
            $border = str_pad($char, $width, $char);

            if ($opts['topBorder']) {
                $topBorder = "  $border\n";
            }

            if ($opts['bottomBorder']) {
                $bottomBorder = "  $border\n";
            }
        }

        $body = is_array($body) ? implode(PHP_EOL, $body) : $body;
        $body = Helper::wrapText($body, 4, $opts['width']);
        self::write(sprintf($tpl, $titleLine, $topBorder, $body, $bottomBorder));
    }

    /**
     * Show a list
     *
     * ```
     * $title = 'list1 title';
     * $data = [
     *      'name'  => 'value text',
     *      'name2' => 'value text 2',
     * ];
     * ```
     * @param $title
     * @param array $data
     * @param array $opts More @see Helper::spliceKeyValue()
     */
    public static function aList($title, $data, array $opts = [])
    {
        // title
        if ( $title ) {
            $title = ucwords(trim($title));

            self::write($title);
        }

        $opts = array_merge([
            'leftChar' => '  ',
            'keyStyle' => 'info',
        ], $opts);

        // item list
        $items = Helper::spliceKeyValue((array)$data, $opts);

        self::write($items);
    }

    /**
     * Show multi list
     *
     * ```
     * [
     *   'list1 title' => [
     *      'name' => 'value text',
     *      'name2' => 'value text 2',
     *   ],
     *   'list2 title' => [
     *      'name' => 'value text',
     *      'name2' => 'value text 2',
     *   ],
     *   ... ...
     * ]
     * ```
     *
     * @param array $data
     * @param array $opts
     */
    public static function multiList(array $data, array $opts = [])
    {
        foreach ($data as $title => $list) {
            self::aList($title, $list, $opts);
        }
    }

    /**
     * Show console help message
     * @param  string $usage    The usage message text. e.g 'command [options] [arguments]'
     * @param  array  $commands The command list
     * e.g
     * [
     *     // command => description
     *     'start'    => 'Start the app server',
     *     ... ...
     * ]
     * @param  array|string  $options The option list
     * e.g
     * [
     *     // option    => description
     *     '-d'         => 'Run the server on daemon.(default: <comment>false</comment>)',
     *     '-h, --help' => 'Display this help message'
     *     ... ...
     * ]
     * @param  array  $examples The command usage example. e.g 'php server.php {start|reload|restart|stop} [-d]'
     * @param  string $description The description text. e.g 'Composer version 1.3.2'
     * @param  bool   $showAfterQuit Show help after quit
     */
    public static function consoleHelp($usage, $commands = [], $options = [], $examples = [], $description = '', $showAfterQuit = true)
    {
        self::helpPanel($usage, $commands, $options, $examples, $description, $showAfterQuit);
    }
    public static function helpPanel($usage, $commands = [], $options = [], $examples = [], $description = '', $showAfterQuit = true)
    {
        // description
        if ( $description ) {
            self::write($description . PHP_EOL);
        }

        // usage
        if ($usage) {
            self::write("<comment>Usage</comment>:\n  {$usage}\n");
        }

        // options list
        if ( $options ) {
            // translate array to string
            if ( is_array($options)) {
                $options = Helper::spliceKeyValue($options, [
                    'leftChar' => '  ',
                    'keyStyle' => 'info',
                ]);
            }

            if ( is_string($options) ) {
                self::write("<comment>Options</comment>:\n{$options}");
            }
        }

        // command list
        if ( $commands ) {
            // translate array to string
            if ( is_array($commands)) {
                $commands = Helper::spliceKeyValue($commands, [
                    'leftChar' => '  ',
                    'keyStyle' => 'info',
                ]);
            }

            if ( is_string($commands) ) {
                self::write("<comment>Commands</comment>:\n{$commands}");
            }
        }

        // examples list
        if ( $examples ) {
            $examples = is_array($examples) ? implode(PHP_EOL . '  ', $examples) : (string)$examples;
            self::write("<comment>Examples</comment>:\n  {$examples}\n");
        }

        if ($showAfterQuit) {
            exit(0);
        }
    }

    /**
     * Show information data panel
     * @param  mixed  $data
     * @param  string $title
     * @param  string $borderChar
     * @return void
     */
    public static function panel($data, $title='Information Panel', $borderChar = '*')
    {
        $data = is_array($data) ? array_filter($data) : [trim($data)];
        $title = trim($title);

        $panelData = []; // [ 'label' => 'value' ]
        $labelMaxWidth = 0; // if label exists, label max width
        $valueMaxWidth = 0; // value max width

        foreach ($data as $label => $value) {
            // label exists
            if ( !is_numeric($label) ) {
                $width = mb_strlen($label, 'UTF-8');
                $labelMaxWidth = $width > $labelMaxWidth ? $width : $labelMaxWidth;
            }

            // translate array to string
            if ( is_array($value) ) {
                $temp = '';

                foreach ($value as $key => $val) {
                    if (is_bool($val)) {
                        $val = $val ? 'True' : 'False';
                    } else {
                        $val = (string)$val;
                    }

                    $temp .= (!is_numeric($key) ? "$key: " : '') . "<info>$val</info>, ";
                }

                $value = rtrim($temp, ' ,');
            } else if (is_bool($value)) {
                $value = $value ? 'True' : 'False';
            } else {
                $value = trim((string)$value);
            }

            // get value width
            $value = trim($value);
            $width = mb_strlen(strip_tags($value), 'UTF-8'); // must clear style tag
            $valueMaxWidth = $width > $valueMaxWidth ? $width : $valueMaxWidth;

            $panelData[$label] = $value;
        }

        $panelWidth = $labelMaxWidth + $valueMaxWidth;

        // output title
        if ($title) {
            $title = ucwords($title);
            $titleLength = mb_strlen($title, 'UTF-8');
            $panelWidth = $panelWidth > $titleLength ? $panelWidth : $titleLength;
            $indentSpace = str_pad(' ', ceil($panelWidth/2) - ceil($titleLength/2) + 2*2, ' ');
            self::write("  {$indentSpace}<bold>{$title}</bold>");
        }

        // output panel top border
        if ($borderChar) {
            $border = str_pad($borderChar, $panelWidth + (3*3), $borderChar);
            self::write('  ' . $border);
        }

        // output panel body
        $panelStr = Helper::spliceKeyValue($panelData, [
            'leftChar'    => "  $borderChar ",
            'sepChar'     => ' | ',
            'keyMaxWidth' => $labelMaxWidth,
        ]);

        // already exists "\n"
        self::write($panelStr, false);

        // output panel bottom border
        if (isset($border)) {
            self::write("  $border\n");
        }

        unset($panelData);
    }

    /**
     * 表格数据信息展示
     * @param  array $data
     * @param  string $title
     * @param  bool $showBorder
     * @return void
     */
    public static function table(array $data, $title='Info List', $showBorder = true)
    {
        $rowIndex = 0;
        $head = $table = [];
        $info = [
            'rowCount'  => count($data),
            'columnCount' => 0,     // how many column in the table.
            'columnMaxWidth' => [], // table column max width
            'tableWidth' => 0,      // table width. equals to all max column width's sum.
        ];

        // parse table data
        foreach ($data as $row) {
            // collection all field name
            if ($rowIndex === 0) {
                $head = array_keys($row);
                $info['columnCount'] = count($row);

                foreach ($head as $index => $name) {
                    $info['columnMaxWidth'][$index] = mb_strlen($name, 'UTF-8');
                }
            }

            $colIndex = 0;

            foreach ($row as $value) {
                // collection column max width
                if ( isset($info['columnMaxWidth'][$colIndex]) ) {
                    $colWidth = mb_strlen($value, 'UTF-8');

                    // If current column width gt old column width. override old width.
                    if ($colWidth > $info['columnMaxWidth'][$colIndex]) {
                        $info['columnMaxWidth'][$colIndex] = $colWidth;
                    }
                } else {
                    $info['columnMaxWidth'][$colIndex] = mb_strlen($value, 'UTF-8');
                }

                $colIndex++;
            }

            $rowIndex++;
        }

        $tableWidth = $info['tableWidth'] = array_sum($info['columnMaxWidth']);
        $columnCount = $info['columnCount'];

        // output title
        if ($title) {
            $title = ucwords(trim($title));
            $titleLength = mb_strlen($title, 'UTF-8');
            $indentSpace = str_pad(' ', ceil($tableWidth/2) - ceil($titleLength/2) + ($columnCount*2), ' ');
            self::write("  {$indentSpace}<bold>{$title}</bold>");
        }

        // output table top border
        if ($showBorder) {
            $border = str_pad('-', $tableWidth + ($columnCount*3) + 2, '-');
            self::write('  ' . $border);
        }

        // output table head
        $headStr = '  | ';
        foreach ($head as $index => $name) {
            $colMaxWidth = $info['columnMaxWidth'][$index];
            $name = str_pad($name, $colMaxWidth, ' ');
            $headStr .= " {$name} |";
        }

        self::write($headStr);

        // border: split head and body
        if (isset($border)) {
            self::write('  ' . $border);
        }

        $rowIndex = 0;

        // output table info
        foreach ($data as $row) {
            $colIndex = 0;
            $rowStr = '  | ';

            foreach ($row as $value) {
                $colMaxWidth = $info['columnMaxWidth'][$colIndex];
                $value = str_pad($value, $colMaxWidth, ' ');
                $rowStr .= " <info>{$value}</info> |";
                $colIndex++;
            }

            self::write("{$rowStr}");

            $rowIndex++;
        }

        // output table bottom border
        if (isset($border)) {
            self::write('  ' . $border);
        }

        echo "\n";
    }


/////////////////////////////////////////////////////////////////
/// Helper Method
/////////////////////////////////////////////////////////////////

    /**
     * @return Color
     */
    public static function getColor()
    {
        return Color::create();
    }

    /**
     * Write a message to standard output stream.
     * @param  string|array $messages    Output message
     * @param  boolean      $nl          true 会添加换行符 false 原样输出，不添加换行符
     * @param  int|boolean  $quit        If is int, setting it is exit code.
     */
    public static function write($messages, $nl = true, $quit = false)
    {
        if ( is_array($messages) ) {
            $messages = implode( $nl ? PHP_EOL : '', $messages );
        }

        $messages = static::getColor()->format($messages);

        fwrite(STDOUT, $messages . ($nl ? PHP_EOL : ''));

        if ( is_int($quit) || true === $quit) {
            $code = true === $quit ? 0 : $quit;
            exit($code);
        }

        fflush(STDOUT);
    }

}