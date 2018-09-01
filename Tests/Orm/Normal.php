<?php
namespace JAF\Tests\Orm;

use PHPUnit\Framework\TestCase;
use JAF\Orm\V2\Demo\Normal as NormalModel;
use JAF\Core\DB\Manager;

class Normal extends TestCase {
    /**
     * @dataProvider additionProviderData
     */
    public function testReadAll($objs) {
        $rows = NormalModel::find();
        $this->assertEquals($rows, [$objs[1], $objs[2], $objs[3], $objs[4]]);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadOne($objs) {
        $row = NormalModel::findOne();
        $this->assertEquals($row, $objs[1]);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadPkExists($objs) {
        $row = NormalModel::findByPk(3);
        $this->assertEquals($row, $objs[3]);
    }

    public function testReadPkNotExists() {
        $row = NormalModel::findByPk(5);
        $this->assertNull($row);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadPksExist($objs) {
        $row = NormalModel::findByPks([2,4]);
        $this->assertEquals($row, [$objs[2], $objs[4]]);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadPksSomeNotExist($objs) {
        $row = NormalModel::findByPks([2, 4, 5]);
        $this->assertEquals($row, [$objs[2], $objs[4]]);
    }

    public function testReadPksSomeNotExistAll() {
        $row = NormalModel::findByPks([5, 9]);
        $data = [];
        $this->assertEquals($row, $data);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadWhereFieldsExist($objs) {
        $rows = NormalModel::fields('a', 'c', 'd')
            ::where('a', '>', 3)
            ::whereRaw('`c` > ? or `d` in (?,?)', [1, 'dx', 'dy'])
            ::where('b', 'in', ['by', 'bz'])
            ::find();
        $data = [$objs[3]];
        $data = array_map(function($obj){
            $r = new NormalModel();
            $r->id = $obj->id;
            $r->a = $obj->a;
            $r->c = $obj->c;
            $r->d = $obj->d;
            $r->tableSuffix = '';
            return $r;
        }, $data);
        $this->assertEquals($rows, $data);
    }

    public function testReadWhereFieldsNotExist() {
        $rows = NormalModel::fields('a', 'c', 'd')
            ::where('a', '>', 9)
            ::whereRaw('`c` > ? or `d` in (?,?)', [1, 'dx', 'dy'])
            ::where('b', 'in', ['by', 'bz'])
            ::find();
        $data = [];
        $this->assertEquals($rows, $data);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadOrder($objs) {
        $rows = NormalModel::order('b', 'desc')
            ::order('c', 'asc')
            ::find();
        $this->assertEquals($rows, [$objs[3], $objs[4], $objs[2], $objs[1]]);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadLimit($objs) {
        $rows = NormalModel::offset(2)
            ::limit(2)
            ::find();
        $this->assertEquals($rows, [$objs[3], $objs[4]]);
    }

    /**
     * @dataProvider additionProviderData
     */
    public function testReadFindComplete($objs) {
        $rows = NormalModel::fields('a', 'c', 'd')
            ::where('a', '>', 2)
            ::whereRaw('`c` > ? or `d` in (?,?)', [1, 'dx', 'dy'])
            ::where('b', 'in', ['by', 'bz'])
            ::order('b', 'desc')
            ::order('c', 'asc')
            ::offset(1)
            ::limit(2)
            ::find();
        $data = [$objs[4], $objs[2]];
        $data = array_map(function($obj){
            $r = new NormalModel();
            $r->id = $obj->id;
            $r->a = $obj->a;
            $r->c = $obj->c;
            $r->d = $obj->d;
            $r->tableSuffix = '';
            return $r;
        }, $data);
        $this->assertEquals($rows, $data);
    }

    public function testReadCount() {
        $cnt = NormalModel::fields('a', 'c', 'd')
            ::where('a', '>', 2)
            ::whereRaw('`c` > ? or `d` in (?,?)', [1, 'dx', 'dy'])
            ::where('b', 'in', ['by', 'bz'])
            ::order('b', 'desc')
            ::order('c', 'asc')
            ::offset(1)
            ::limit(1)
            ::count();
        $this->assertEquals($cnt, 3);
    }

    public function testReadCountNotExist() {
        $cnt = NormalModel::fields('a', 'c', 'd')
            ::where('a', '>', 10)
            ::whereRaw('`c` > ? or `d` in (?,?)', [1, 'dx', 'dy'])
            ::where('b', 'in', ['by', 'bz'])
            ::order('b', 'desc')
            ::order('c', 'asc')
            ::offset(1)
            ::limit(1)
            ::count();
        $this->assertEquals($cnt, 0);
    }

    public function testReadSum() {
        $sum = NormalModel::fields('a', 'c', 'd')
            ::where('a', '>', 2)
            ::whereRaw('`c` > ? or `d` in (?,?)', [1, 'dx', 'dy'])
            ::where('b', 'in', ['by', 'bz'])
            ::order('b', 'desc')
            ::order('c', 'asc')
            ::offset(1)
            ::sum('c');
        $this->assertEquals($sum, 9);
    }

    public function testReadSumNotExist() {
        $sum = NormalModel::fields('a', 'c', 'd')
            ::where('a', '>', 10)
            ::whereRaw('`c` > ? or `d` in (?,?)', [1, 'dx', 'dy'])
            ::where('b', 'in', ['by', 'bz'])
            ::order('b', 'desc')
            ::order('c', 'asc')
            ::offset(1)
            ::sum('c');
        $this->assertNull($sum);
    }

    public function testWriteInsert() {
        $obj = new NormalModel();
        $obj->a = 1;
        $obj->b = 'bbb';
        $obj->c = 2;
        $obj->d = 'ddd';
        $id = $obj->insert();
        $data = NormalModel::findByPk($id);
        $this->assertEquals($obj, $data);
        $data->delete();
    }

    public function testWriteInsertExist() {
        $obj = new NormalModel();
        $obj->id = 1;
        $obj->a = 1;
        $obj->b = 'bbb';
        $obj->c = 2;
        $obj->d = 'ddd';
        try {
            $id = $obj->insert();
        } catch (\Exception $e) {
            $id = 0;
        }
        $this->assertEquals($id, 0);
    }

    public function testWriteUpdate() {
        $init = new NormalModel();
        $init->a = 1;
        $init->b = 'bbb';
        $init->c = 2;
        $init->d = 'ddd';
        $id = $init->insert();

        $data = NormalModel::findByPk($id);

        $obj = new NormalModel();
        $obj->id = $id;
        $obj->a = $data->a;
        $obj->b = $data->b.'1';
        $obj->c = $data->c+1;
        $obj->d = $data->d;
        $obj->tableSuffix = '';

        $data->update(['b'=>$data->b.'1', 'c'=>$data->c+1]);
        $data = NormalModel::findByPk($id);
        $this->assertEquals($obj, $data);

        $data->delete();
    }

    public function testWriteUpdateWithLock() {
        $init = new NormalModel();
        $init->a = 1;
        $init->b = 'bbb';
        $init->c = 2;
        $init->d = 'ddd';
        $id = $init->insert();

        $data = NormalModel::findByPk($id);

        $obj = clone($data);
        $obj->a = $data->a;
        $obj->b = $data->b.'1';
        $obj->c = $data->c + 1;
        $obj->d = $data->d;

        $res = $obj->update(['b'=>$data->b.'1', 'c'=>$data->c+1], ['d'=>'dddd']);
        $this->assertEquals($res, 0);

        $dataNew = NormalModel::findByPk($id);
        $this->assertEquals($data, $dataNew);

        $data->delete();
    }

    public function testWriteDelete() {
        $init = new NormalModel();
        $init->a = 1;
        $init->b = 'bbb';
        $init->c = 2;
        $init->d = 'ddd';
        $id = $init->insert();

        $init->delete();
        $data = NormalModel::findByPk($id);
        $this->assertNull($data);
    }

    public function testWriteDeleteFind() {
        $obj = new NormalModel();
        $obj->a = 1;
        $obj->b = 'bbb';
        $obj->c = 2;
        $obj->d = 'ddd';
        $id = $obj->insert();
        $data = NormalModel::findByPk($id);
        $data->delete();
        $dataNew = NormalModel::findByPk($id);
        $this->assertNull($dataNew);
    }

    public function testWriteTransactionRollBack() {
        $pdo = Manager::get_instance()->get_pdo('xhj_dev');
        $pdo->beginTransaction();
        $obj = new NormalModel();
        $obj->a = 2;
        $obj->b = 'ccc';
        $obj->c = 3;
        $obj->d = 'ddd';
        $id = $obj->insert();
        $pdo->rollBack();
        $data = NormalModel::findByPk($id);
        $this->assertNull($data);
    }

    public function testWriteTransactionCommit() {
        $pdo = Manager::get_instance()->get_pdo('xhj_dev');
        $pdo->beginTransaction();
        $obj = new NormalModel();
        $obj->a = 2;
        $obj->b = 'ccc';
        $obj->c = 3;
        $obj->d = 'ddd';
        $id = $obj->insert();
        $pdo->commit();
        $data = NormalModel::findByPk($id);

        $objNew = new NormalModel();
        $objNew->id = $id;
        $objNew->a = 2;
        $objNew->b = 'ccc';
        $objNew->c = 3;
        $objNew->d = 'ddd';
        $objNew->tableSuffix = '';
        $this->assertEquals($data, $objNew);

        $obj->delete();
    }

    public function additionProviderData() {
        $obj1 = new NormalModel();
        $obj1->id = 1;
        $obj1->a = 6;
        $obj1->b = 'bx';
        $obj1->c = 1;
        $obj1->d = 'dz';
        $obj1->tableSuffix = '';

        $obj2 = new NormalModel();
        $obj2->id = 2;
        $obj2->a = 3;
        $obj2->b = 'by';
        $obj2->c = 2;
        $obj2->d = 'dy';
        $obj2->tableSuffix = '';

        $obj3 = new NormalModel();
        $obj3->id = 3;
        $obj3->a = 8;
        $obj3->b = 'bz';
        $obj3->c = 3;
        $obj3->d = 'dx';
        $obj3->tableSuffix = '';

        $obj4 = new NormalModel();
        $obj4->id = 4;
        $obj4->a = 3;
        $obj4->b = 'bz';
        $obj4->c = 4;
        $obj4->d = 'dy';
        $obj4->tableSuffix = '';

        return [
            [
                'objs' => [1=>$obj1, 2=>$obj2, 3=>$obj3, 4=>$obj4]
            ]
        ];
    }
}