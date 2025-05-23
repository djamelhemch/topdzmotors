<?php
/**
 * Operation handler (last modified: 2025.04.10).
 *
 * This file is a part of the "common classes package", utilised by a number of
 * packages and projects, including CIDRAM and phpMussel.
 * @link https://github.com/Maikuolan/Common
 *
 * License: GNU/GPLv2
 * @see LICENSE.txt
 *
 * "COMMON CLASSES PACKAGE" COPYRIGHT 2019 and beyond by Caleb Mazalevskis.
 * *This particular class*, COPYRIGHT 2021 and beyond by Caleb Mazalevskis.
 */

namespace Maikuolan\Common;

class Operation extends CommonAbstract
{
    /**
     * @var array Caching to optimise operations.
     */
    private $Cache = [];

    /**
     * Operators for version numbers.
     *
     * @param string $Actual The actual value.
     * @param string $Constraint The constraint to meet.
     * @param bool $NextMajor Whether to limit against next major.
     * @param bool $GreaterThan Whether operator is greater than.
     * @param bool $OrEqualTo Whether operator is or equal to.
     * @return bool True if constraint is met; False otherwise.
     */
    public function opVersions(string $Actual, string $Constraint, bool $NextMajor, bool $GreaterThan, bool $OrEqualTo): bool
    {
        $Actual = $this->splitVersionParts($Actual);
        $Constraint = $this->splitVersionParts($Constraint);
        if ($NextMajor && ($Actual[0] !== $Constraint[0])) {
            return false;
        }
        while (true) {
            $ActualPart = array_shift($Actual);
            $ConstraintPart = array_shift($Constraint);
            if ($ActualPart === null && $ConstraintPart === null) {
                break;
            }
            if ($ActualPart === null) {
                $ActualPart = 0;
            }
            if ($ConstraintPart === null) {
                $ConstraintPart = 0;
            }
            if ($ActualPart < $ConstraintPart) {
                return !$GreaterThan;
            }
            if ($ActualPart > $ConstraintPart) {
                return $GreaterThan;
            }
        }
        return $OrEqualTo;
    }

    /**
     * Equal to operator.
     *
     * @param string $Actual The actual value.
     * @param string $Constraint The constraint to meet.
     * @return bool True if constraint is met; False otherwise.
     */
    public function opEqualTo(string $Actual, string $Constraint): bool
    {
        $Actual = $this->splitVersionParts($Actual);
        $Constraint = $this->splitVersionParts($Constraint);
        while (true) {
            $ActualPart = array_shift($Actual);
            $ConstraintPart = array_shift($Constraint);
            if ($ActualPart === null && $ConstraintPart === null) {
                break;
            }
            if ($ActualPart !== $ConstraintPart) {
                return false;
            }
        }
        return true;
    }

    /**
     * Multiple compare operation.
     *
     * @param array $Operand Left-side operands.
     * @param array $Prefix Operations and right-side operands.
     * @return bool Return value of the operation.
     */
    public function multiCompare(array $Operand, array $Prefix): bool
    {
        $Result = false;
        while ($ThisOperand = array_shift($Operand)) {
            $ThisPrefix = array_shift($Prefix);
            $Result = $this->singleCompare($ThisOperand, $ThisPrefix);
            if (!$Result) {
                return false;
            }
        }
        return $Result;
    }

    /**
     * Single compare operation.
     *
     * @param string $Operand Left-side operand.
     * @param string $Prefix Operation and right-side operand.
     * @return bool Return value of the operation.
     */
    public function singleCompare(string $Operand, string $Prefix): bool
    {
        if (isset($this->Cache[$Operand . $Prefix])) {
            return $this->Cache[$Operand . $Prefix];
        }
        if ($Prefix === '*') {
            return ($this->Cache[$Operand . $Prefix] = strlen($Operand) > 0);
        }
        $OpOrArr = explode('|', $Prefix);
        $Result = true;
        foreach ($OpOrArr as $OpOr) {
            $OpAndArr = preg_split('~\&| ~', $OpOr, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            foreach ($OpAndArr as $OpAnd) {
                $Initial = substr($OpAnd, 0, 1);
                if ($Initial === '>') {
                    $OpAnd = substr($OpAnd, 1);
                    $Initial = substr($OpAnd, 0, 1);
                    if ($Initial === '=') {
                        $Result = $this->opVersions($Operand, substr($OpAnd, 1), false, true, true);
                    } else {
                        $Result = $this->opVersions($Operand, $OpAnd, false, true, false);
                    }
                } elseif ($Initial === '<') {
                    $OpAnd = substr($OpAnd, 1);
                    $Initial = substr($OpAnd, 0, 1);
                    if ($Initial === '=') {
                        $Result = $this->opVersions($Operand, substr($OpAnd, 1), false, false, true);
                    } else {
                        $Result = $this->opVersions($Operand, $OpAnd, false, false, false);
                    }
                } elseif ($Initial === '^') {
                    $Result = $this->opVersions($Operand, substr($OpAnd, 1), true, true, true);
                } elseif ($Initial === '=') {
                    $Result = $this->opEqualTo($Operand, substr($OpAnd, 1));
                } else {
                    $Result = $this->opEqualTo($Operand, $OpAnd);
                }
                if (!$Result) {
                    continue 2;
                }
            }
            if ($Result) {
                break;
            }
        }
        return ($this->Cache[$Operand . $Prefix] = $Result);
    }

    /**
     * Split version parts.
     *
     * @param string $Version The version to split.
     * @return array The split version.
     */
    public function splitVersionParts(string $Version = ''): array
    {
        $Version = preg_replace(
            ['~^v\.?~', '~[-_+]~', '~(\d)([a-z])~', '~([a-z])(\d)~'],
            ['', '.', '\1.\2', '\1.\2'],
            strtolower($Version)
        );
        $Parts = explode('.', $Version);
        foreach ($Parts as &$Part) {
            if (preg_match('~^([a-z])~', $Part, $Initial)) {
                if ($Initial[0] === 'd') {
                    $Part = -5;
                } elseif ($Initial[0] === 'a') {
                    $Part = -4;
                } elseif ($Initial[0] === 'b') {
                    $Part = -3;
                } elseif ($Initial[0] === 'r') {
                    $Part = -2;
                } elseif ($Initial[0] === 'p') {
                    $Part = -1;
                } elseif (strlen($Initial[0]) > 0) {
                    $Part = -6;
                } else {
                    $Part = 0;
                }
            } else {
                $Part = (int)$Part;
            }
        }
        $PartsPad = 3 - count($Parts);
        while ($PartsPad > 0) {
            $Parts[] = 0;
            $PartsPad--;
        }
        return $Parts;
    }

    /**
     * If compare operation.
     *
     * @param mixed $Data The data to traverse.
     * @param string $IfString The if string.
     * @param bool $AllowMethodCalls Whether to allow method calls.
     * @return string The results of the operation (or an empty string on failure).
     */
    public function ifCompare(&$Data, string $IfString, bool $AllowMethodCalls = false): string
    {
        $LCIfString = strtolower($IfString);

        /** Guard. */
        if (substr($LCIfString, 0, 3) !== 'if ') {
            $IfString = trim($IfString);
            if (substr($IfString, 0, 1) === '{' && substr($IfString, -1) === '}') {
                $IfString = $this->dataTraverse($Data, substr($IfString, 1, -1), false, $AllowMethodCalls);
            }
            return $IfString;
        }

        $IfString = substr($IfString, 3);
        $LCIfString = substr($LCIfString, 3);
        if (($ElsePos = strpos($LCIfString, ' else')) === false) {
            $ElseString = '';
        } else {
            $ElseString = substr($IfString, $ElsePos + 5);
            $IfString = substr($IfString, 0, $ElsePos);
            $LCIfString = strtolower($IfString);
        }
        if (($ThenPos = strpos($LCIfString, ' then')) === false) {
            $ThenString = '';
        } else {
            $ThenString = substr($IfString, $ThenPos + 5);
            $IfString = substr($IfString, 0, $ThenPos);
            if (substr($ThenString, 0, 1) === '{') {
                if (substr($ThenString, -1) === '}') {
                    $ThenString = substr($ThenString, 1, -1);
                } elseif (substr($ElseString, 0, 1) !== '{' && substr($ElseString, -1) === '}') {
                    $ThenString = substr($ThenString, 1) . ' else' . substr($ElseString, 0, -1);
                    $ElseString = '';
                }
            }
        }
        if (substr($ElseString, 0, 1) === '{' && substr($ElseString, -1) === '}') {
            $ElseString = substr($ElseString, 1, -1);
        }

        /** Process condition. */
        foreach (explode('||', $IfString) as $PartsOr) {
            $IfPass = true;
            foreach (explode('&&', $PartsOr) as $PartsAnd) {
                $Parts = preg_split('~([<>]=?|!?[=^]+)~', $PartsAnd, -1, PREG_SPLIT_DELIM_CAPTURE);
                foreach ($Parts as &$Part) {
                    $Part = trim($Part);
                    if (substr($Part, 0, 1) === '{' && substr($Part, -1) === '}') {
                        $Part = $this->dataTraverse($Data, substr($Part, 1, -1), false, $AllowMethodCalls);
                    }
                }
                $CParts = count($Parts);
                if (($CParts % 2) === 0) {
                    $IfPass = false;
                    continue 2;
                } elseif ($CParts === 1) {
                    if (!$Parts[0]) {
                        $IfPass = false;
                    }
                    continue 2;
                } elseif ($CParts === 3) {
                    if ($Parts[1] === '===') {
                        $Try = ($Parts[0] === $Parts[2]);
                    } elseif ($Parts[1] === '!==') {
                        $Try = ($Parts[0] !== $Parts[2]);
                    } elseif ($Parts[1] === '==') {
                        $Try = ($Parts[0] == $Parts[2]);
                    } elseif ($Parts[1] === '!=') {
                        $Try = ($Parts[0] != $Parts[2]);
                    } else {
                        $Initial = substr($Parts[1], 0, 1);
                        if ($Initial === '=' || $Initial === '^') {
                            $Parts[1] = $Initial;
                        }
                        $Try = $this->singleCompare($Parts[0], $Parts[1] . $Parts[2]);
                    }
                    if (!$Try) {
                        $IfPass = false;
                        continue 2;
                    }
                }
            }
            if ($IfPass) {
                break;
            }
        }

        if ($IfPass) {
            return $this->ifCompare($Data, $ThenString, $AllowMethodCalls);
        }
        if ($ElseString) {
            return $this->ifCompare($Data, $ElseString, $AllowMethodCalls);
        }
        return '';
    }

    /**
     * Set operation.
     *
     * @param mixed $Data Where to set the data.
     * @param string $Instruction The instruction.
     * @param bool $AllowMethodCalls Whether to allow method calls.
     * @return bool True on success; False on failure.
     */
    public function set(&$Data, string $Instruction, bool $AllowMethodCalls = false): bool
    {
        if (strpos($Instruction, "\n") !== false) {
            $Instruction = explode("\n", $Instruction);
            $AllGood = true;
            foreach ($Instruction as $Task) {
                if (!$this->set($Data, $Task, $AllowMethodCalls)) {
                    $AllGood = false;
                    break;
                }
            }
            return $AllGood;
        }
        $LCInstruction = strtolower($Instruction);
        if (substr($LCInstruction, 0, 3) === 'if ') {
            $Instruction = $this->ifCompare($Data, $Instruction, $AllowMethodCalls);
        }
        if ($Instruction === '') {
            return true;
        }
        $Parts = preg_split('~([-+*/.]?=)~', $Instruction, 2, PREG_SPLIT_DELIM_CAPTURE);
        if (count($Parts) !== 3) {
            return false;
        }
        if (substr($Parts[2], 0, 1) === '{' && substr($Parts[2], -1) === '}') {
            $Parts[2] = $this->dataTraverse($Data, substr($Parts[2], 1, -1), true, $AllowMethodCalls);
        }
        $Path = preg_split('~(?<!\\\\)\\.~', $Parts[0]) ?: [];
        foreach ($Path as $Segment) {
            if (!$Segment && $Segment !== 0 && $Segment !== '0') {
                return false;
            }
            $Segment = str_replace('\.', '.', $Segment);
            if (!preg_match('~\D~', $Segment)) {
                $Segment = (int)$Segment;
            }
            if (is_object($Data)) {
                if (property_exists($Data, $Segment)) {
                    $Data = &$Data->$Segment;
                    continue;
                }
                return false;
            }
            if (!is_array($Data)) {
                $Data = [];
            }
            if (!isset($Data[$Segment])) {
                $Data[$Segment] = [];
            }
            $Data = &$Data[$Segment];
        }
        if ($Parts[1] === '=') {
            $Data = $Parts[2];
            return true;
        }
        if ($Parts[1] === '-=') {
            if (is_numeric($Data) && is_numeric($Parts[2])) {
                $Data -= $Parts[2];
                return true;
            }
            return false;
        }
        if ($Parts[1] === '+=') {
            if (is_numeric($Data) && is_numeric($Parts[2])) {
                $Data += $Parts[2];
                return true;
            }
            return false;
        }
        if ($Parts[1] === '*=') {
            if (is_numeric($Data) && is_numeric($Parts[2])) {
                $Data *= $Parts[2];
                return true;
            }
            return false;
        }
        if ($Parts[1] === '/=') {
            if (is_numeric($Data) && is_numeric($Parts[2]) && $Parts[2] !== 0) {
                $Data /= $Parts[2];
                return true;
            }
            return false;
        }
        if ($Parts[1] === '.=') {
            if (is_scalar($Data) && is_scalar($Parts[2])) {
                $Data .= $Parts[2];
                return true;
            }
        }
        return false;
    }
}
