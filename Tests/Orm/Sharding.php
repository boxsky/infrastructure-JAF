<?php
namespace JAF\Tests\Orm;

use PHPUnit\Framework\TestCase;
use JAF\Orm\V2\Demo\Sharding as ShardingModel;
use JAF\Orm\V2\Demo\Sharding2 as ShardingModel2;
use JAF\Core\DB\Manager;

class Sharding extends TestCase {
    public function testRoute() {
        $model = new ShardingModel();
        $suffix1 = $model->table_suffix_route(['id' => 2]);
        $suffix2 = $model->table_suffix_route(['id' => 5]);
        $this->assertEquals($suffix1, 2);
        $this->assertEquals($suffix2, 1);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadAll($objs) {
        $rows = ShardingModel::set_table_suffix(1)
                             ::find();
        $this->assertEquals($rows, [$objs[1], $objs[3]]);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadOne($objs) {
        $row = ShardingModel::set_table_suffix(2)
                            ::findOne();
        $this->assertEquals($row, $objs[2]);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadPkExists($objs) {
        $row = ShardingModel::set_table_suffix(1)
                            ::findByPk(3);
        $this->assertEquals($row, $objs[3]);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadPkNotExists() {
        $row = ShardingModel::set_table_suffix(1)
                            ::findByPk(2);
        $this->assertNull($row);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadPksExist($objs) {
        $row = ShardingModel::set_table_suffix(2)
                            ::findByPks([2,4]);
        $this->assertEquals($row, [$objs[2], $objs[4]]);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadPksSomeNotExist($objs) {
        $row = ShardingModel::set_table_suffix(2)
                            ::findByPks([2, 4, 5]);
        $this->assertEquals($row, [$objs[2], $objs[4]]);
    }

    public function testReadPksSomeNotExistAll() {
        $row = ShardingModel::set_table_suffix(1)
                            ::findByPks([5, 9]);
        $data = [];
        $this->assertEquals($row, $data);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadWhereFieldsExist($objs) {
        $rows = ShardingModel::set_table_suffix(1)
                             ::fields('a')
                             ::where('a', '=', 'a')
                             ::whereRaw('`a` <> ?', ['b'])
                             ::find();
        $data = [$objs[1]];
        $data = array_map(function($obj){
            $r = new ShardingModel();
            $r->id = $obj->id;
            $r->a = $obj->a;
            $r->tableSuffix = '1';
            return $r;
        }, $data);
        $this->assertEquals($rows, $data);
    }

    public function testReadWhereFieldsNotExist() {
        $rows = ShardingModel::set_table_suffix(2)
                             ::fields('a')
                             ::where('a', '=', 'b')
                             ::whereRaw('`a` <> ?', ['b'])
                             ::find();
        $data = [];
        $this->assertEquals($rows, $data);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadOrder($objs) {
        $rows = ShardingModel::set_table_suffix(2)
                             ::order('a', 'desc')
                             ::find();
        $this->assertEquals($rows, [$objs[4], $objs[2]]);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadLimit($objs) {
        $rows = ShardingModel::set_table_suffix(1)
                             ::offset(1)
                             ::limit(1)
                             ::find();
        $this->assertEquals($rows, [$objs[3]]);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadFindComplete($objs) {
        $rows = ShardingModel::set_table_suffix(2)
                             ::fields('a')
                             ::where('a', '=', 'd')
                             ::whereRaw('`a` <> ?', ['b'])
                             ::order('a', 'desc')
                             ::offset(0)
                             ::limit(1)
                             ::find();
        $data = [$objs[4]];
        $data = array_map(function($obj){
            $r = new ShardingModel();
            $r->id = $obj->id;
            $r->a = $obj->a;
            $r->tableSuffix =$obj->tableSuffix;
            return $r;
        }, $data);
        $this->assertEquals($rows, $data);
    }

    public function testReadCount() {
        $cnt = ShardingModel::set_table_suffix(2)
                            ::fields('a')
                            ::where('a', '=', 'd')
                            ::whereRaw('`a` <> ?', ['b'])
                            ::order('a', 'desc')
                            ::offset(0)
                            ::limit(1)
                            ::count();
        $this->assertEquals($cnt, 1);
    }

    public function testReadCountNotExist() {
        $cnt = ShardingModel::set_table_suffix(2)
                            ::fields('a')
                            ::where('a', '=', 'd')
                            ::whereRaw('`a` <> ?', ['d'])
                            ::order('a', 'desc')
                            ::offset(0)
                            ::limit(1)
                            ::count();
        $this->assertEquals($cnt, 0);
    }

    public function testReadSum() {
        $sum = ShardingModel::set_table_suffix(2)
                            ::sum('id');
        $this->assertEquals($sum, 6);
    }

    public function testReadSumNotExist() {
        $sum = ShardingModel::set_table_suffix(2)
                            ::where('a', '=', 'a')
                            ::sum('id');
        $this->assertNull($sum);
    }

    public function testWriteInsert1() {
        $obj = new ShardingModel();
        $obj->id = 5;
        $obj->a = 'a';
        $id = $obj->insert();
        $data = ShardingModel::set_table_suffix(1)
                             ::findByPk($id);
        $this->assertEquals($obj, $data);
        $data->delete();
    }

    public function testWriteInsert2() {
        $obj = new ShardingModel2();
        $obj->set_table_suffix(1);
        $obj->id = 5;
        $obj->a = 'a';
        $id = $obj->insert();
        $data = ShardingModel2::set_table_suffix(1)
                              ::findByPk($id);
        $this->assertEquals($obj, $data);
        $data->delete();
    }

    public function testWriteInsertExist() {
        $obj = new ShardingModel();
        $obj->id = 1;
        $obj->a = 'a';
        try {
            $id = $obj->insert();
        } catch (\Exception $e) {
            $id = -1;
        }
        $this->assertEquals($id, -1);
    }

    public function testWriteUpdate() {
        $init = new ShardingModel();
        $init->id = 5;
        $init->a = 'e';
        $id = $init->insert();

        $data = ShardingModel::set_table_suffix(1)
                             ::findByPk($id);

        $obj = new ShardingModel();
        $obj->id = $id;
        $obj->a = $data->a.'1';
        $obj->tableSuffix = '1';

        $data->update(['a'=>$data->a.'1']);
        $data = ShardingModel::set_table_suffix(1)
                             ::findByPk($id);
        $this->assertEquals($obj, $data);

        $data->delete();
    }

    public function testWriteUpdateWithLock() {
        $init = new ShardingModel();
        $init->id = 5;
        $init->a = 'e';
        $id = $init->insert();

        $data = ShardingModel::set_table_suffix(1)
                             ::findByPk($id);
        $obj = clone($data);
        $obj->a = $data->a.'1';
        $res = $obj->update(['a'=>$data->a.'1'], ['a'=>'ddd']);
        $this->assertEquals($res, 0);

        $dataNew = ShardingModel::set_table_suffix(1)
                                ::findByPk($id);
        $this->assertEquals($data, $dataNew);

        $data->delete();
    }

    public function testWriteDelete() {
        $init = new ShardingModel();
        $init->id = 5;
        $init->a = 'e';
        $id = $init->insert();

        $init->delete();
        $data = ShardingModel::set_table_suffix(1)
                             ::findByPk($id);
        $this->assertNull($data);
    }

    public function testWriteTransactionRollBack() {
        $pdo = Manager::get_instance()->get_pdo('xhj_dev');
        $pdo->beginTransaction();
        $obj = new ShardingModel();
        $obj->id = 5;
        $obj->a = 'aaaaaa';
        $id = $obj->insert();
        $pdo->rollBack();
        $data = ShardingModel::set_table_suffix(1)
                             ::findByPk($id);
        $this->assertNull($data);
    }

    public function testWriteTransactionCommit() {
        $pdo = Manager::get_instance()->get_pdo('xhj_dev');
        $pdo->beginTransaction();
        $obj = new ShardingModel();
        $obj->id = 5;
        $obj->a = 'aaaaaaa';
        $id = $obj->insert();
        $pdo->commit();
        $data = ShardingModel::set_table_suffix(1)
                             ::findByPk($id);
        $objNew = new ShardingModel();
        $objNew->id = $id;
        $objNew->a = 'aaaaaaa';
        $objNew->tableSuffix = '1';
        $this->assertEquals($data, $objNew);

        $data->delete();
    }

    public static function additionProviderData() {
        $obj1 = new ShardingModel();
        $obj1->id = 1;
        $obj1->a = 'a';
        $obj1->tableSuffix = '1';

        $obj2 = new ShardingModel();
        $obj2->id = 2;
        $obj2->a = 'b';
        $obj2->tableSuffix = '2';

        $obj3 = new ShardingModel();
        $obj3->id = 3;
        $obj3->a = 'c';
        $obj3->tableSuffix = '1';

        $obj4 = new ShardingModel();
        $obj4->id = 4;
        $obj4->a = 'd';
        $obj4->tableSuffix = '2';

        return [
            [
                'objs' => [1=>$obj1, 2=>$obj2, 3=>$obj3, 4=>$obj4]
            ]
        ];
    }
}