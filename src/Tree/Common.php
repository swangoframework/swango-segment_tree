<?php
namespace PHPSegmentTree\Tree;
class Common extends \PHPSegmentTree\AbstractSegmentTree {
    /**
     * Create a new tree by left position and right position
     *
     * @param string $l
     * @param string $r
     * @return SegmentTree
     */
    public static function newTree(int $l, int $r): Common {
        if ($l > $r)
            throw new \Exception('Left position cannot be larger than right');
        return new self($l, $r);
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
        return $segments;
    }
    /**
     * Get a fixed array the size of whole tree filled with values of every position.
     * The fixed array start with zero and values \PHPSegmentTree\SegmentTreeValueNotFoundException for every empty position.
     *
     * @param string $key
     * @return \SplFixedArray
     */
    public function toFixedArray(string $key): \SplFixedArray {
        $fixed_array = new \SplFixedArray($this->r - $this->l + 1);
        $this->_getFixedArray($key, $fixed_array, $this->l);
        return $fixed_array;
    }
    /**
     * Fill $key between $l and $r by $value
     *
     * @param int $l
     * @param int $r
     * @param string $key
     * @param mixed $value
     * @throws \OutOfRangeException
     * @return self
     */
    public function setValue(int $l, int $r, string $key, $value): self {
        if ($l > $r || $l < $this->l || $r > $this->r)
            throw new \OutOfRangeException("Position out of range! l:$l,r:$r out of [$this->l, $this->r]");
        $this->_setValue($l, $r, $key, $value);
        return $this;
    }
    /**
     *
     * Delete $key between $l and $r by $value. Noted it's different from setValue($l, $r, $key, null)
     *
     * @param int $l
     * @param int $r
     * @param string $key
     * @throws \OutOfRangeException
     * @return self
     */
    public function delValue(int $l, int $r, string $key): self {
        if ($l > $r || $l < $this->l || $r > $this->r)
            throw new \OutOfRangeException("Position out of range! l:$l,r:$r out of [$this->l, $this->r]");
        $this->_delValue($l, $r, $key);
        return $this;
    }
    /**
     *
     * @param int $position
     * @param string $key
     * @throws \PHPSegmentTree\SegmentTreeValueNotFoundException Thorws when the given key is not set on $position
     * @throws \OutOfRangeException
     * @return mixed
     */
    public function getValue(int $position, string $key) {
        if ($position < $this->l || $position > $this->r)
            throw new \OutOfRangeException("Position out of range! position:$position out of [$this->l, $this->r]");
        return $this->_getValue($position, $key);
    }
    /**
     * determin if the given $key exists between $l and $r, no matter the value
     *
     * @param string $l
     * @param string $r
     * @param string $key
     * @throws \OutOfRangeException
     * @return bool
     */
    public function exists(int $l, int $r, string $key): bool {
        if ($l > $r || $l < $this->l || $r > $this->r)
            throw new \OutOfRangeException("Position out of range! l:$l,r:$r out of [$this->l, $this->r]");
        return $this->_exists($l, $r, $key);
    }
    /**
     * Get the left position and right position of the tree
     *
     * @return array [$l, $r]
     */
    public function getLAndR(): array {
        return [
            $this->l,
            $this->r
        ];
    }
}
