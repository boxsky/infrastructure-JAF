<?php
namespace JAF\Job;

abstract class ParentJob {
    public function main($argv=[]) {
        $this->handle_main($this->normalize($argv));
    }

    private function normalize($argv) {
        $args = array();
        if ($argv) {
            foreach ($argv as $i => $str) {
                if (strpos($str, '--') !== 0) continue;
                $str = preg_replace('/^--/', '', $str);
                parse_str($str, $arr);
                foreach ($arr as $k => $v) {
                    //允许参数传入数组,eg:[1,2,3]
                    if (preg_match('/^\[(.*)\]$/', $v, $matches)) {
                        $tmp = array_filter(explode(',', $matches[1]));
                        if (count($tmp)>1) {
                            $arr[$k] = $tmp;
                        }
                    }
                }
                $args = array_merge($args, $arr);
            }
        }
        return $args;
    }

    abstract function handle_main($params);
}