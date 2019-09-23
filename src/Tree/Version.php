<?php
namespace Swango\SegmentTree\Tree;
class Version extends \Swango\SegmentTree\AbstractSegmentTree {
    /**
     *
     * @var \SplFixedArray $compressed_version_map
     * @var array $version_compressed_map
     */
    protected $compressed_version_map, $version_compressed_map;
    /**
     * Create a tree by given versions.
     * They will be sorted using version_compare() ascending
     *
     * @param string ...$version
     * @return Common
     */
    public static function newTree(string ...$version): Version {
        $versions = array_unique($version);
        $length = count($versions);
        $compressed_version_map = new \SplFixedArray($length - 1);
        foreach ($versions as $i=>$v)
            $compressed_version_map[$i] = $v;
        self::qsortVersions($compressed_version_map, 0, $length - 1);
        $version_compressed_map = [];
        for($i = 0; $i < $length; ++ $i)
            $version_compressed_map[$compressed_version_map[$i]] = $i;

        $root = new self(0, $length);
        $root->compressed_version_map = $compressed_version_map;
        $root->version_compressed_map = $version_compressed_map;
        return $root;
    }
    /**
     * Create a tree by given sorted versions.
     *
     * @param \SplFixedArray $versions
     * @return Version
     */
    public static function newTreeWithSortedVersions(string ...$version): Version {
        $versions = array_unique($version);
        $length = count($versions);
        $compressed_version_map = new \SplFixedArray($length - 1);
        foreach ($versions as $i=>$v)
            $compressed_version_map[$i] = $v;
        $version_compressed_map = [];
        for($i = 0; $i < $length; ++ $i)
            $version_compressed_map[$compressed_version_map[$i]] = $i;

        $root = new self(0, $length);
        $root->compressed_version_map = $compressed_version_map;
        $root->version_compressed_map = $version_compressed_map;
        return $root;
    }
    /**
     * Quick sort
     *
     * @param \SplFixedArray $arr
     * @param int $l
     * @param int $r
     */
    protected static function qsortVersions(\SplFixedArray $arr, int $l, int $r) {
        $i = $l;
        $j = $r;
        $x = $arr[intdiv($l + $r, 2)];
        $t = null;
        do {
            while ( version_compare($arr[$i], $x) === - 1 )
                ++ $i;
            while ( version_compare($x, $arr[$j]) === - 1 )
                -- $j;
            if ($i <= $j) {
                $t = $arr[$i];
                $arr[$i] = $arr[$j];
                $arr[$j] = $t;
                ++ $i;
                -- $j;
            }
        } while ( $i <= $j );
        unset($t);
        unset($x);
        if ($i < $r)
            self::qsortVersions($arr, $i, $r);
        if ($l < $j)
            self::qsortVersions($arr, $l, $j);
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
            $min_version = reset($v);
            $max_version = end($v);
            $v = [
                $this->compressed_version_map[$min_version],
                $this->compressed_version_map[$max_version]
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
            $min_version = reset($v);
            $max_version = $v[1];
            $v = [
                $this->compressed_version_map[$min_version],
                $this->compressed_version_map[$max_version],
                end($v)
            ];
        }
        return $segments;
    }
    /**
     * Get a fixed array the size of whole tree filled with values of every position.
     * The fixed array start with zero and values \Swango\SegmentTree\SegmentTreeValueNotFoundException for every empty position.
     *
     * @param string $key
     * @return array
     */
    public function toArray(string $key): array {
        $fixed_array = new \SplFixedArray($this->r - $this->l + 1);
        $this->_getFixedArray($key, $fixed_array, $this->l);
        $ret = [];
        for($i = 0, $l = $fixed_array->getSize(); $i < $l; ++ $i)
            $ret[$this->compressed_version_map[$i]] = $fixed_array[$i];
        return $ret;
    }
    /**
     * Fill $key between $from_version and $to_version by $value
     *
     * @param string $from_version
     * @param string $to_version
     * @param string $key
     * @param mixed $value
     * @throws \OutOfRangeException
     * @return self
     */
    public function setValue(?string $from_version, ?string $to_version, string $key, $value): self {
        if (isset($from_version))
            $l = $this->version_compressed_map[$from_version];
        else
            $l = $this->l;
        if (isset($to_version))
            $r = $this->version_compressed_map[$to_version];
        else
            $r = $this->r;
        if ($l > $r || $l < $this->l || $r > $this->r)
            throw new \OutOfRangeException("Position out of range! l:$l,r:$r out of [$this->l, $this->r]");
        $this->_setValue($l, $r, $key, $value);
        return $this;
    }
    /**
     *
     * Delete $key between $from_version and $to_version by $value. Noted it's different from setValue($from_version, $to_version, $key, null)
     *
     * @param string $from_version
     * @param string $to_version
     * @param string $key
     * @throws \OutOfRangeException
     * @return self
     */
    public function delValue(?string $from_version, ?string $to_version, string $key): self {
        if (isset($from_version))
            $l = $this->version_compressed_map[$from_version];
        else
            $l = $this->l;
        if (isset($to_version))
            $r = $this->version_compressed_map[$to_version];
        else
            $r = $this->r;
        if ($l > $r || $l < $this->l || $r > $this->r)
            throw new \OutOfRangeException("Position out of range! l:$l,r:$r out of [$this->l, $this->r]");
        $this->_delValue($l, $r, $key);
        return $this;
    }
    /**
     *
     * @param string $version
     * @param string $key
     * @throws \Swango\SegmentTree\SegmentTreeValueNotFoundException Thorws when the given key is not set on $position
     * @throws \OutOfRangeException
     * @return mixed
     */
    public function getValue(string $version, string $key) {
        $position = $this->version_compressed_map[$version];
        if ($position < $this->l || $position > $this->r)
            throw new \OutOfRangeException("Position out of range! position:$position out of [$this->l, $this->r]");
        return $this->_getValue($position, $key);
    }
    /**
     * determin if the given $key exists between $from_version and $to_version, no matter the value
     *
     * @param string $from_version
     * @param string $to_version
     * @param string $key
     * @throws \OutOfRangeException
     * @return bool
     */
    public function exists(?string $from_version, ?string $to_version, string $key): bool {
        if (isset($from_version))
            $l = $this->version_compressed_map[$from_version];
        else
            $l = $this->l;
        if (isset($to_version))
            $r = $this->version_compressed_map[$to_version];
        else
            $r = $this->r;
        if ($l > $r || $l < $this->l || $r > $this->r)
            throw new \OutOfRangeException("Position out of range! l:$l,r:$r out of [$this->l, $this->r]");
        return $this->_exists($l, $r, $key);
    }
    /**
     * Get the min version and max version of the tree
     *
     * @return array [$l, $r]
     */
    public function getMinVersionAndMaxVersion(): array {
        return [
            $this->compressed_version_map[$this->l],
            $this->compressed_version_map[$this->r]
        ];
    }
    /**
     * Determin if given version is valid
     *
     * @param string $version
     * @return bool
     */
    public function versionValid(string $version): bool {
        return array_key_exists($version, $this->version_compressed_map);
    }
    /**
     *
     * @param string $version
     * @return string|NULL
     */
    public function getVersionBefore(string $version): ?string {
        $version = $this->version_compressed_map[$version];
        if ($version === 0)
            return null;
        return $this->compressed_version_map[$version - 1];
    }
}