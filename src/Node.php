<?php
namespace Swango\SegmentTree;
abstract class Node implements \Countable {
    public function count() {
        return $this->r - $this->l + 1;
    }
    abstract protected function isEqual($x, $y): bool;
    /**
     *
     * @var int $l
     * @var int $r
     * @var Node $node_l
     * @var Node $node_r
     * @var object $data
     */
    protected $l, $r, $node_l, $node_r, $data;
    protected function __construct(int $l, int $r) {
        $this->l = $l;
        $this->r = $r;
        $this->data = new \stdClass();
    }
    protected function hasChildNode(): bool {
        return isset($this->node_l);
    }
    protected function isLeafNode(): bool {
        return $this->l === $this->r;
    }
    protected function getMiddle(): int {
        return intdiv($this->l + $this->r, 2);
    }
    protected function _getValue(int $position, string $key) {
        if (! property_exists($this->data, $key))
            throw new SegmentTreeValueNotFoundException();

        $value = $this->data->{$key};
        if ($value instanceof MixtureFlag) {
            if ($position <= $this->getMiddle())
                return $this->node_l->_getValue($position, $key);
            else
                return $this->node_r->_getValue($position, $key);
        } else {
            return $value;
        }
    }
    protected function _exists(int $l, int $r, string $key): bool {
        if (! property_exists($this->data, $key))
            return false;
        if (! $this->data->{$key} instanceof MixtureFlag)
            return true;

        $middle = $this->getMiddle();
        if ($l <= $middle && $this->node_l->_exists($l, $r, $key))
            return true;

        if ($r > $middle && $this->node_r->_exists($l, $r, $key))
            return true;

        return false;
    }
    protected function _getSegmentsOfGivenKeyAndValue(string $key, $value, array &$segments): void {
        if (! property_exists($this->data, $key))
            return;
        if ($this->data->{$key} instanceof MixtureFlag) {
            $this->node_l->_getSegmentsOfGivenKeyAndValue($key, $value, $segments);
            $this->node_r->_getSegmentsOfGivenKeyAndValue($key, $value, $segments);
        } elseif ($this->isEqual($this->data->{$key}, $value)) {
            $l = count($segments);
            if ($l === 0) {
                $segments[] = [
                    $this->l,
                    $this->r
                ];
            } else {
                $end = &$segments[$l - 1];
                if (end($end) === $this->l - 1)
                    $end[1] = $this->r;
                else
                    $segments[] = [
                        $this->l,
                        $this->r
                    ];
            }
        }
    }
    protected function _getSegmentsOfGivenKey(string $key, array &$segments): void {
        if (! property_exists($this->data, $key))
            return;
        if ($this->data->{$key} instanceof MixtureFlag) {
            $this->node_l->_getSegmentsOfGivenKey($key, $segments);
            $this->node_r->_getSegmentsOfGivenKey($key, $segments);
        } else {
            $l = count($segments);
            if ($l === 0) {
                $segments[] = [
                    $this->l,
                    $this->r,
                    $this->data->{$key}
                ];
            } else {
                $end = &$segments[$l - 1];
                if ($end[1] === $this->l - 1 && $this->isEqual($this->data->{$key}, end($end)))
                    $end[1] = $this->r;
                else
                    $segments[] = [
                        $this->l,
                        $this->r,
                        $this->data->{$key}
                    ];
            }
        }
    }
    protected function _getFixedArray(string $key, \SplFixedArray $fixed_array, int $offset): void {
        if (! property_exists($this->data, $key)) {
            for($i = $this->l; $i <= $this->r; ++ $i)
                $fixed_array[$i - $offset] = new SegmentTreeValueNotFoundException();
            return;
        }
        $value = $this->data->{$key};
        if ($value instanceof MixtureFlag) {
            $this->node_l->_getFixedArray($key, $fixed_array, $offset);
            $this->node_r->_getFixedArray($key, $fixed_array, $offset);
        } else {
            for($i = $this->l; $i <= $this->r; ++ $i)
                $fixed_array[$i - $offset] = $value;
        }
    }
    protected function _setValue(int $l, int $r, string $key, $value): void {
        if (property_exists($this->data, $key)) {
            $current_value = $this->data->{$key};
            if ($this->isEqual($current_value, $value))
                return;
        }

        if ($l <= $this->l && $this->r <= $r) {
            $this->data->{$key} = $value;
        } else {
            $middle = $this->getMiddle();
            $this->createChildNode($middle);
            if (property_exists($this->data, $key) && ! $current_value instanceof MixtureFlag) {
                $this->node_l->data->{$key} = $current_value;
                $this->node_r->data->{$key} = $current_value;
            }

            if ($l <= $middle)
                $this->node_l->_setValue($l, $r, $key, $value);

            if ($r > $middle)
                $this->node_r->_setValue($l, $r, $key, $value);

            if (property_exists($this->node_l->data, $key) && property_exists($this->node_r->data, $key) &&
                 $this->isEqual($this->node_l->data->{$key}, $value) &&
                 $this->isEqual($this->node_r->data->{$key}, $value)) {
                $this->data->{$key} = $value;
            } else {
                $this->data->{$key} = MixtureFlag::getInstance();
            }
        }
    }
    protected function _delValue(int $l, int $r, string $key): void {
        if (! property_exists($this->data, $key))
            return;

        if ($l <= $this->l && $this->r <= $r) {
            unset($this->data->{$key});
        } else {
            $middle = $this->getMiddle();
            $this->createChildNode($middle);
            $current_value = $this->data->{$key};
            if (! $current_value instanceof MixtureFlag) {
                $this->node_l->data->{$key} = $current_value;
                $this->node_r->data->{$key} = $current_value;
            }

            if ($l <= $middle)
                $this->node_l->_delValue($l, $r, $key);

            if ($r > $middle)
                $this->node_r->_delValue($l, $r, $key);

            if (property_exists($this->node_l->data, $key) || property_exists($this->node_r->data, $key)) {
                $this->data->{$key} = MixtureFlag::getInstance();
            } else
                unset($this->data->{$key});
        }
    }
    protected function createChildNode(int $middle): void {
        if (! $this->hasChildNode()) {
            $this->node_l = new static($this->l, $middle);
            $this->node_r = new static($middle + 1, $this->r);
        }
    }
}