<?php
namespace Swango\SegmentTree\Tree;
class Common extends \Swango\SegmentTree\AbstractSegmentTree {
    /**
     * Create a new tree by left position and right position
     *
     * @param string $l
     * @param string $r
     * @return Common
     */
    public static function newTree(int $l, int $r): Common {
        if ($l > $r) {
            throw new \Exception('Left position cannot be larger than right');
        }
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
     * The fixed array start with zero and values \Swango\SegmentTree\SegmentTreeValueNotFoundException for every empty position.
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
     * @return self
     * @throws \OutOfRangeException
     */
    public function setValue(int $l, int $r, string $key, $value): self {
        if ($l > $r || $l < $this->l || $r > $this->r) {
            throw new \OutOfRangeException("Position out of range! l:$l,r:$r out of [$this->l, $this->r]");
        }
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
     * @return self
     * @throws \OutOfRangeException
     */
    public function delValue(int $l, int $r, string $key): self {
        if ($l > $r || $l < $this->l || $r > $this->r) {
            throw new \OutOfRangeException("Position out of range! l:$l,r:$r out of [$this->l, $this->r]");
        }
        $this->_delValue($l, $r, $key);
        return $this;
    }
    /**
     *
     * @param int $position
     * @param string $key
     * @return mixed
     * @throws \OutOfRangeException
     * @throws \Swango\SegmentTree\SegmentTreeValueNotFoundException Throws when the given key is not set on $position
     */
    public function getValue(int $position, string $key) {
        if ($position < $this->l || $position > $this->r) {
            throw new \OutOfRangeException("Position out of range! position:$position out of [$this->l, $this->r]");
        }
        return $this->_getValue($position, $key);
    }
    /**
     * @param int $position
     * @return array
     * @throws \OutOfRangeException
     */
    public function getAllValue(int $position): array {
        if ($position < $this->l || $position > $this->r) {
            throw new \OutOfRangeException("Position out of range! position:$position out of [$this->l, $this->r]");
        }
        $result = [];
        $this->_getData($position, $result);
        return $result;
    }
    /**
     * Determine if the given $key exists between $l and $r, no matter the value
     *
     * @param string $l
     * @param string $r
     * @param string $key
     * @return bool
     * @throws \OutOfRangeException
     */
    public function exists(int $l, int $r, string $key): bool {
        if ($l > $r || $l < $this->l || $r > $this->r) {
            throw new \OutOfRangeException("Position out of range! l:$l,r:$r out of [$this->l, $this->r]");
        }
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
