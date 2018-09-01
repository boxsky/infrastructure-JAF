<?php
namespace JAF\Tests\Orm;

use PHPUnit\Framework\TestCase;
use JAF\Orm\V2\Demo\PKDistributor as PKDistributorModel;
use JAF\Orm\V2\Demo\PKDistributorUse4Testing;

class PKDistributor extends TestCase {
    public function testDistribute() {
        $current_row = PKDistributorUse4Testing::findOne();
        $current_id = $current_row ? $current_row->id : 0;
        $new_id = PKDistributorModel::distribute();
        $this->assertEquals($new_id, $current_id+1);
    }
}