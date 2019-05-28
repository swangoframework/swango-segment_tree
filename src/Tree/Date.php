<?php
namespace PHPSegmentTree\Tree;
class Date extends \PHPSegmentTree\AbstractSegmentTree {
    protected $date_offset = 0;
    /**
     *
     * Create a new tree by min date and max date
     *
     * @param mixed $min_date
     *            string date or unix timestamp in second
     * @param mixed $max_date
     *            string date or unix timestamp in second
     * @throws \OutOfRangeException
     * @return Date
     */
    public static function newTree($min_date, $max_date): Date {
        $date_offset = self::convertDate($min_date, 0);
        $to_date = self::convertDate($max_date, $date_offset);
        if ($to_date < 0)
            throw new \OutOfRangeException('To date out of range! $to_date before $from_date');
        $root = new self(0, $to_date);
        $root->date_offset = $date_offset;
        return $root;
    }
    protected static function convertDate($date, int $date_offset): int {
        if (is_string($date))
            return intdiv(strtotime($date), 86400) - $date_offset;
        else
            return intdiv($date, 86400) - $date_offset;
    }
    protected static function toStringDate(int $position, int $date_offset): string {
        return date('Y-m-d', ($position + $date_offset) * 86400);
    }
    /**
     *
     * @param string $key
     * @param mixed $value
     * @return array [[$seg1_l, $seg1_r], [$seg2_l, $seg2_r], [$seg3_l, $seg3_r], ...]
     */
    public function getSegmentsOfGivenKeyAndValue(string $key, $value): array {
        $segments = [];
        $this->_getSegmentsOfGivenKeyAndValue($key, $value, $segments);
        foreach ($segments as &$v) {
            $l = reset($v);
            $r = end($v);
            $v = [
                self::toStringDate($l, $this->date_offset),
                self::toStringDate($r, $this->date_offset)
            ];
        }
        return $segments;
    }
    /**
     *
     * @param string $key
     * @param mixed $value
     * @return array [[$seg1_l, $seg1_r, $value1], [$seg2_l, $seg2_r, $value2], [$seg3_l, $seg3_r, $value3], ...]
     */
    public function getSegmentsOfGivenKey(string $key): array {
        $segments = [];
        $this->_getSegmentsOfGivenKey($key, $segments);
        foreach ($segments as &$v) {
            $l = reset($v);
            $r = $v[1];
            $v = [
                self::toStringDate($l, $this->date_offset),
                self::toStringDate($r, $this->date_offset),
                end($v)
            ];
        }
        return $segments;
    }
    /**
     * Get a fixed array the size of whole tree filled with values of every position.
     * The fixed array start with zero and values \PHPSegmentTree\SegmentTreeValueNotFoundException for every empty position.
     *
     * @param string $key
     * @return array
     */
    public function toArray(string $key): array {
        $fixed_array = new \SplFixedArray($this->r - $this->l + 1);
        $this->_getFixedArray($key, $fixed_array, $this->l);
        $ret = [];
        for($i = 0, $l = $fixed_array->getSize(); $i < $l; ++ $i)
            $ret[self::toStringDate($i, $this->date_offset)] = $fixed_array[$i];
        return $ret;
    }
    /**
     * Fill $key between $from_date and $to_date by $value
     *
     * @param mixed $from_date
     * @param mixed $to_date
     * @param string $key
     * @param mixed $value
     * @throws \OutOfRangeException
     * @return self
     */
    public function setValue($from_date, $to_date, string $key, $value): self {
        if (isset($from_date))
            $l = self::convertDate($from_date, $this->date_offset);
        else
            $l = $this->l;
        if (isset($to_date))
            $r = self::convertDate($to_date, $this->date_offset);
        else
            $r = $this->r;
        if ($l > $r || $l < $this->l || $r > $this->r)
            throw new \OutOfRangeException("Position out of range! l:$l,r:$r out of [$this->l, $this->r]");
        $this->_setValue($l, $r, $key, $value);
        return $this;
    }
    /**
     *
     * Delete $key between $from_date and $to_date by $value. Noted it's different from setValue($from_date, $to_date, $key, null)
     *
     * @param mixed $from_date
     * @param mixed $to_date
     * @param string $key
     * @throws \OutOfRangeException
     * @return self
     */
    public function delValue($from_date, $to_date, string $key): self {
        if (isset($from_date))
            $l = self::convertDate($from_date, $this->date_offset);
        else
            $l = $this->l;
        if (isset($to_date))
            $r = self::convertDate($to_date, $this->date_offset);
        else
            $r = $this->r;
        if ($l > $r || $l < $this->l || $r > $this->r)
            throw new \OutOfRangeException("Position out of range! l:$l,r:$r out of [$this->l, $this->r]");
        $this->_delValue($l, $r, $key);
        return $this;
    }
    /**
     *
     * @param mixed $date
     * @param string $key
     * @throws \PHPSegmentTree\SegmentTreeValueNotFoundException Thorws when the given key is not set on $position
     * @throws \OutOfRangeException
     * @return mixed
     */
    public function getValue($date, string $key) {
        $position = self::convertDate($date, $this->date_offset);
        if ($position < $this->l || $position > $this->r)
            throw new \OutOfRangeException("Position out of range! position:$position out of [$this->l, $this->r]");
        return $this->_getValue($position, $key);
    }
    /**
     * determin if the given $key exists between $from_date and $to_date, no matter the value
     *
     * @param mixed $from_date
     * @param mixed $to_date
     * @param string $key
     * @throws \OutOfRangeException
     * @return bool
     */
    public function exists($from_date, $to_date, string $key): bool {
        if (isset($from_date))
            $l = self::convertDate($from_date, $this->date_offset);
        else
            $l = $this->l;
        if (isset($to_date))
            $r = self::convertDate($to_date, $this->date_offset);
        else
            $r = $this->r;
        if ($l > $r || $l < $this->l || $r > $this->r)
            throw new \OutOfRangeException("Position out of range! l:$l,r:$r out of [$this->l, $this->r]");
        return $this->_exists($l, $r, $key);
    }
    /**
     * Get the min date and max date of the tree
     *
     * @return array [$l, $r]
     */
    public function getMinDateAndMaxDate(): array {
        return [
            self::toStringDate($this->l, $this->date_offset),
            self::toStringDate($this->r, $this->date_offset)
        ];
    }
}