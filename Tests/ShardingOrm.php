<?php
namespace JAF\Tests;

use JAF\Orm\Demo\TestWriteSharding2;
use PHPUnit\Framework\TestCase;
use JAF\Orm\Demo\TestReadSharding;
use JAF\Orm\Demo\TestWriteSharding;
use JAF\Core\DB\Manager;

class ShardingOrm extends TestCase {
    public function testReadAll() {
        $rows = TestReadSharding::set_table_suffix(1)
                                ::find();
        $data = array_values(self::get_data_all_1());
        $this->assertEquals($rows, $data);
    }

    public function testReadOne() {
        $row = TestReadSharding::set_table_suffix(2)
                               ::findOne();
        $data = self::get_data_all_2();
        $data = $data['obj2'];
        $this->assertEquals($row, $data);
    }

    public function testReadPkExists() {
        $row = TestReadSharding::set_table_suffix(1)
                               ::findByPk(3);
        $data = self::get_data_all_1();
        $data = $data['obj3'];
        $this->assertEquals($row, $data);
    }

    public function testReadPkNotExists() {
        $row = TestReadSharding::set_table_suffix(1)
                               ::findByPk(2);
        $this->assertNull($row);
    }

    public function testReadPksExist() {
        $row = TestReadSharding::set_table_suffix(2)
                               ::findByPks([2,4]);
        $data = self::get_data_all_2();
        $data = [$data['obj2'], $data['obj4']];
        $this->assertEquals($row, $data);
    }

    public function testReadPksSomeNotExist() {
        $row = TestReadSharding::set_table_suffix(2)
                               ::findByPks([2, 4, 5]);
        $data = self::get_data_all_2();
        $data = [$data['obj2'], $data['obj4']];
        $this->assertEquals($row, $data);
    }

    public function testReadPksSomeNotExistAll() {
        $row = TestReadSharding::set_table_suffix(1)
                               ::findByPks([5, 9]);
        $data = [];
        $this->assertEquals($row, $data);
    }

    public function testReadWhereFieldsExist() {
        $rows = TestReadSharding::set_table_suffix(1)
                                ::fields('a')
                                ::where('a', '=', 'a')
                                ::whereRaw('`a` <> ?', ['b'])
                                ::find();
        $data = self::get_data_all_1();
        $data = [$data['obj1']];
        $data = array_map(function($obj){
            $r = new TestReadSharding();
            $r->id = $obj->id;
            $r->a = $obj->a;
            $r->isLoaded = $obj->isLoaded;
            $r->tableNameFinal = 't_orm_read_sharding_1';
            return $r;
        }, $data);
        $this->assertEquals($rows, $data);
    }

    public function testReadWhereFieldsNotExist() {
        $rows = TestReadSharding::set_table_suffix(2)
                                ::fields('a')
                                ::where('a', '=', 'b')
                                ::whereRaw('`a` <> ?', ['b'])
                                ::find();
        $data = [];
        $this->assertEquals($rows, $data);
    }

    public function testReadOrder() {
        $rows = TestReadSharding::set_table_suffix(2)
                                ::order('a', 'desc')
                                ::find();
        $data = self::get_data_all_2();
        $data = [$data['obj4'], $data['obj2']];
        $this->assertEquals($rows, $data);
    }

    public function testReadLimit() {
        $rows = TestReadSharding::set_table_suffix(1)
                                ::offset(1)
                                ::limit(1)
                                ::find();
        $data = self::get_data_all_1();
        $data = [$data['obj3']];
        $this->assertEquals($rows, $data);
    }

    public function testReadFindComplete() {
        $rows = TestReadSharding::set_table_suffix(2)
                                ::fields('a')
                                ::where('a', '=', 'd')
                                ::whereRaw('`a` <> ?', ['b'])
                                ::order('a', 'desc')
                                ::offset(0)
                                ::limit(1)
                                ::find();
        $data = self::get_data_all_2();
        $data = [$data['obj4']];
        $data = array_map(function($obj){
            $r = new TestReadSharding();
            $r->id = $obj->id;
            $r->a = $obj->a;
            $r->isLoaded =$obj->isLoaded;
            $r->tableNameFinal =$obj->tableNameFinal;
            return $r;
        }, $data);
        $this->assertEquals($rows, $data);
    }

    public function testReadCount() {
        $cnt = TestReadSharding::set_table_suffix(2)
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
        $cnt = TestReadSharding::set_table_suffix(2)
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
        $sum = TestReadSharding::set_table_suffix(2)
                               ::sum('id');
        $this->assertEquals($sum, 6);
    }

    public function testReadSumNotExist() {
        $sum = TestReadSharding::set_table_suffix(2)
                               ::where('a', '=', 'a')
                               ::sum('id');
        $this->assertNull($sum);
    }

    public function testWriteInsert1() {
        $obj = TestWriteSharding::set_table_suffix(1)
                                ::findByPk(1);
        if (!is_null($obj)) {
            $obj->delete();
        }
        $obj = new TestWriteSharding();
        $obj->id = 1;
        $obj->a = 'a';
        $id = $obj->save();
        $data = TestWriteSharding::set_table_suffix(1)
                                 ::findByPk($id);
        $this->assertEquals($obj, $data);
    }

    public function testWriteInsert2() {
        $obj = TestWriteSharding::set_table_suffix(1)
                                ::findByPk(1);
        if (!is_null($obj)) {
            $obj->delete();
        }
        $obj = new TestWriteSharding2();
        $obj->set_table_suffix(1);
        $obj->id = 1;
        $obj->a = 'a';
        $id = $obj->save();
        $data = TestWriteSharding2::set_table_suffix(1)
                                  ::findByPk($id);
        $this->assertEquals($obj, $data);
    }

    public function testWriteInsertExist() {
        $obj = new TestWriteSharding();
        $obj->id = 1;
        $obj->a = 'a';
        try {
            $id = $obj->save();
        } catch (\Exception $e) {
            $id = 0;
        }
        $this->assertEquals($id, 0);
    }

    public function testWriteUpdate() {
        $data = TestWriteSharding::set_table_suffix(1)
                                 ::findByPk(1);
        $obj = new TestWriteSharding();
        $obj->id = 1;
        $obj->a = $data->a.'1';
        $obj->isLoaded = true;
        $obj->tableNameFinal = 't_orm_write_sharding_1';
        $data->a = $data->a.'1';
        $data->save();
        $data = TestWriteSharding::set_table_suffix(1)
                                 ::findByPk(1);
        $this->assertEquals($obj, $data);
    }

    public function testWriteUpdateWithLock() {
        $data = TestWriteSharding::set_table_suffix(1)
                                 ::findByPk(1);
        $obj = clone($data);
        $obj->a = $data->a;
        $res = $obj->saveWithLock(['a'=>'a11']);
        $this->assertEquals($res, 0);

        $dataNew = TestWriteSharding::set_table_suffix(1)
                                    ::findByPk(1);
        $this->assertEquals($data, $dataNew);
    }

    public function testWriteUpdateV2() {
        $data = TestWriteSharding::set_table_suffix(1)
                                 ::findByPk(1);
        $obj = new TestWriteSharding();
        $obj->id = 1;
        $obj->a = $data->a.'1';
        $obj->isLoaded = true;
        $obj->tableNameFinal = 't_orm_write_sharding_1';
        $updateArr = [
            'a' => $data->a.'1'
        ];
        $data->update($updateArr);
        $data = TestWriteSharding::set_table_suffix(1)
                                 ::findByPk(1);
        $this->assertEquals($obj, $data);
    }

    public function testWriteUpdateWithLockV2() {
        $data = TestWriteSharding::set_table_suffix(1)
                                 ::findByPk(1);
        $obj = clone($data);
        $updateArr = [
            'a' => $data->a.'1'
        ];
        $res = $obj->update($updateArr, ['a'=>'dddd']);
        $this->assertEquals($res, 0);

        $dataNew = TestWriteSharding::set_table_suffix(1)
                                    ::findByPk(1);
        $this->assertEquals($data, $dataNew);
    }

    public function testWriteDelete() {
        $data = TestWriteSharding::set_table_suffix(1)
                                 ::findByPk(3);
        if (!is_null($data)) {
            $data->delete();
        }
        $obj = new TestWriteSharding();
        $obj->id = 3;
        $obj->a = 'aaa';
        $id = $obj->save();
        $obj->delete();
        $data = TestWriteSharding::set_table_suffix(1)
                                 ::findByPk($id);
        $this->assertNull($data);
    }

    public function testWriteTransactionRollBack() {
        $pdo = Manager::get_instance()->get_pdo('xhj_dev');
        $pdo->beginTransaction();
        $obj = new TestWriteSharding();
        $obj->id = 3;
        $obj->a = 'aaaaaa';
        $id = $obj->save();
        $pdo->rollBack();
        $data = TestWriteSharding::set_table_suffix(1)
                                 ::findByPk($id);
        $this->assertNull($data);
    }

    public function testWriteTransactionCommit() {
        $pdo = Manager::get_instance()->get_pdo('xhj_dev');
        $pdo->beginTransaction();
        $obj = new TestWriteSharding();
        $obj->id = 3;
        $obj->a = 'aaaaaaa';
        $id = $obj->save();
        $pdo->commit();
        $data = TestWriteSharding::set_table_suffix(1)
                                 ::findByPk($id);
        $objNew = new TestWriteSharding();
        $objNew->id = $id;
        $objNew->a = 'aaaaaaa';
        $objNew->isLoaded = true;
        $objNew->tableNameFinal = 't_orm_write_sharding_1';
        $this->assertEquals($data, $objNew);
    }

    private static function get_data_all_1() {
        $obj1 = new TestReadSharding();
        $obj1->id = 1;
        $obj1->a = 'a';
        $obj1->isLoaded = true;
        $obj1->tableNameFinal = 't_orm_read_sharding_1';

        $obj3 = new TestReadSharding();
        $obj3->id = 3;
        $obj3->a = 'c';
        $obj3->isLoaded = true;
        $obj3->tableNameFinal = 't_orm_read_sharding_1';

        return [
            'obj1' => $obj1,
            'obj3' => $obj3,
        ];
    }

    private static function get_data_all_2() {
        $obj2 = new TestReadSharding();
        $obj2->id = 2;
        $obj2->a = 'b';
        $obj2->isLoaded = true;
        $obj2->tableNameFinal = 't_orm_read_sharding_2';

        $obj4 = new TestReadSharding();
        $obj4->id = 4;
        $obj4->a = 'd';
        $obj4->isLoaded = true;
        $obj4->tableNameFinal = 't_orm_read_sharding_2';

        return [
            'obj2' => $obj2,
            'obj4' => $obj4
        ];
    }
}