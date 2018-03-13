<?php
namespace JAF\Tests;

use PHPUnit\Framework\TestCase;
use JAF\Orm\Demo\TestRead;
use JAF\Orm\Demo\TestWrite;
use JAF\Core\DB\Manager;

class Orm extends TestCase {
    public function testReadAll() {
        $rows = TestRead::find();
        $data = array_values(self::get_data_all());
        $this->assertEquals($rows, $data);
    }

    public function testReadOne() {
        $row = TestRead::findOne();
        $data = self::get_data_all();
        $data = $data['obj1'];
        $this->assertEquals($row, $data);
    }

    public function testReadPkExists() {
        $row = TestRead::findByPk(3);
        $data = self::get_data_all();
        $data = $data['obj3'];
        $this->assertEquals($row, $data);
    }

    public function testReadPkNotExists() {
        $row = TestRead::findByPk(5);
        $this->assertNull($row);
    }

    public function testReadPksExist() {
        $row = TestRead::findByPks([2,4]);
        $data = self::get_data_all();
        $data = [$data['obj2'], $data['obj4']];
        $this->assertEquals($row, $data);
    }

    public function testReadPksSomeNotExist() {
        $row = TestRead::findByPks([2, 4, 5]);
        $data = self::get_data_all();
        $data = [$data['obj2'], $data['obj4']];
        $this->assertEquals($row, $data);
    }

    public function testReadPksSomeNotExistAll() {
        $row = TestRead::findByPks([5, 9]);
        $data = [];
        $this->assertEquals($row, $data);
    }

    public function testReadWhereFieldsExist() {
        $rows = TestRead::fields('a', 'c', 'd')
                        ::where('a', '>', 3)
                        ::whereRaw('`c` > ? or `d` in (?,?)', [1, 'dx', 'dy'])
                        ::where('b', 'in', ['by', 'bz'])
                        ::find();
        $data = self::get_data_all();
        $data = [$data['obj3']];
        $data = array_map(function($obj){
            $r = new TestRead();
            $r->id = $obj->id;
            $r->a = $obj->a;
            $r->c = $obj->c;
            $r->d = $obj->d;
            $r->isLoaded =$obj->isLoaded;
            return $r;
        }, $data);
        $this->assertEquals($rows, $data);
    }

    public function testReadWhereFieldsNotExist() {
        $rows = TestRead::fields('a', 'c', 'd')
            ::where('a', '>', 9)
            ::whereRaw('`c` > ? or `d` in (?,?)', [1, 'dx', 'dy'])
            ::where('b', 'in', ['by', 'bz'])
            ::find();
        $data = [];
        $this->assertEquals($rows, $data);
    }

    public function testReadOrder() {
        $rows = TestRead::order('b', 'desc')
                        ::order('c', 'asc')
                        ::find();
        $data = self::get_data_all();
        $data = [$data['obj3'], $data['obj4'], $data['obj2'], $data['obj1']];
        $this->assertEquals($rows, $data);
    }

    public function testReadLimit() {
        $rows = TestRead::offset(2)
                        ::limit(2)
                        ::find();
        $data = self::get_data_all();
        $data = [$data['obj3'], $data['obj4']];
        $this->assertEquals($rows, $data);
    }

    public function testReadFindComplete() {
        $rows = TestRead::fields('a', 'c', 'd')
            ::where('a', '>', 2)
            ::whereRaw('`c` > ? or `d` in (?,?)', [1, 'dx', 'dy'])
            ::where('b', 'in', ['by', 'bz'])
            ::order('b', 'desc')
            ::order('c', 'asc')
            ::offset(1)
            ::limit(2)
            ::find();
        $data = self::get_data_all();
        $data = [$data['obj4'], $data['obj2']];
        $data = array_map(function($obj){
            $r = new TestRead();
            $r->id = $obj->id;
            $r->a = $obj->a;
            $r->c = $obj->c;
            $r->d = $obj->d;
            $r->isLoaded =$obj->isLoaded;
            return $r;
        }, $data);
        $this->assertEquals($rows, $data);
    }

    public function testReadCount() {
        $cnt = TestRead::fields('a', 'c', 'd')
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
        $cnt = TestRead::fields('a', 'c', 'd')
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
        $sum = TestRead::fields('a', 'c', 'd')
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
        $sum = TestRead::fields('a', 'c', 'd')
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
        $obj = new TestWrite();
        $obj->a = 1;
        $obj->b = 'bbb';
        $obj->c = 2;
        $obj->d = 'ddd';
        $id = $obj->save();
        $data = TestWrite::findByPk($id);
        $this->assertEquals($obj, $data);
    }

    public function testWriteInsertExist() {
        $obj = new TestWrite();
        $obj->id = 1;
        $obj->a = 1;
        $obj->b = 'bbb';
        $obj->c = 2;
        $obj->d = 'ddd';
        try {
            $id = $obj->save();
        } catch (\Exception $e) {
            $id = 0;
        }
        $this->assertEquals($id, 0);
    }

    public function testWriteUpdate() {
        $data = TestWrite::findByPk(1);
        $obj = new TestWrite();
        $obj->id = 1;
        $obj->a = $data->a;
        $obj->b = $data->b.'1';
        $obj->c = $data->c + 1;
        $obj->d = $data->d;
        $obj->isLoaded = true;
        $data->b = $data->b.'1';
        $data->c = $data->c + 1;
        $data->save();
        $data = TestWrite::findByPk(1);
        $this->assertEquals($obj, $data);
    }

    public function testWriteUpdateWithLock() {
        $data = TestWrite::findByPk(1);

        $obj = clone($data);
        $obj->a = $data->a;
        $obj->b = $data->b.'1';
        $obj->c = $data->c + 1;
        $obj->d = $data->d;
        $res = $obj->saveWithLock(['d'=>'dddd']);
        $this->assertEquals($res, 0);

        $dataNew = TestWrite::findByPk(1);
        $this->assertEquals($data, $dataNew);
    }

    public function testWriteDelete() {
        $obj = new TestWrite();
        $obj->a = 1;
        $obj->b = 'bbb';
        $obj->c = 2;
        $obj->d = 'ddd';
        $id = $obj->save();
        $obj->delete();
        $data = TestWrite::findByPk($id);
        $this->assertNull($data);
    }

    public function testWriteDeleteFind() {
        $obj = new TestWrite();
        $obj->a = 1;
        $obj->b = 'bbb';
        $obj->c = 2;
        $obj->d = 'ddd';
        $id = $obj->save();
        $data = TestWrite::findByPk($id);
        $data->delete();
        $dataNew = TestWrite::findByPk($id);
        $this->assertNull($dataNew);
    }

    public function testWriteTransactionRollBack() {
        $pdo = Manager::get_instance()->get_pdo('xhj_dev');
        $pdo->beginTransaction();
        $obj = new TestWrite();
        $obj->a = 2;
        $obj->b = 'ccc';
        $obj->c = 3;
        $obj->d = 'ddd';
        $id = $obj->save();
        $pdo->rollBack();
        $data = TestWrite::findByPk($id);
        $this->assertNull($data);
    }

    public function testWriteTransactionCommit() {
        $pdo = Manager::get_instance()->get_pdo('xhj_dev');
        $pdo->beginTransaction();
        $obj = new TestWrite();
        $obj->a = 2;
        $obj->b = 'ccc';
        $obj->c = 3;
        $obj->d = 'ddd';
        $id = $obj->save();
        $pdo->commit();
        $data = TestWrite::findByPk($id);
        $objNew = new TestWrite();
        $objNew->id = $id;
        $objNew->a = 2;
        $objNew->b = 'ccc';
        $objNew->c = 3;
        $objNew->d = 'ddd';
        $objNew->isLoaded = true;
        $this->assertEquals($data, $objNew);
    }

    private static function get_data_all() {
        $obj1 = new TestRead();
        $obj1->id = 1;
        $obj1->a = 6;
        $obj1->b = 'bx';
        $obj1->c = 1;
        $obj1->d = 'dz';
        $obj1->isLoaded = true;

        $obj2 = new TestRead();
        $obj2->id = 2;
        $obj2->a = 3;
        $obj2->b = 'by';
        $obj2->c = 2;
        $obj2->d = 'dy';
        $obj2->isLoaded = true;

        $obj3 = new TestRead();
        $obj3->id = 3;
        $obj3->a = 8;
        $obj3->b = 'bz';
        $obj3->c = 3;
        $obj3->d = 'dx';
        $obj3->isLoaded = true;

        $obj4 = new TestRead();
        $obj4->id = 4;
        $obj4->a = 3;
        $obj4->b = 'bz';
        $obj4->c = 4;
        $obj4->d = 'dy';
        $obj4->isLoaded = true;

        return [
            'obj1' => $obj1,
            'obj2' => $obj2,
            'obj3' => $obj3,
            'obj4' => $obj4
        ];
    }
}