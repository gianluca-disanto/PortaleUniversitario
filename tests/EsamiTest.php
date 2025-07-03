<?php

use PHPUnit\Framework\TestCase;



class EsamiTest extends TestCase{
    function testGetEsami(){
        $expected = [
            'id' => '15',
            'nome_esame' => 'SWBD',
            'data_esame' => '15/02/2025',
            'ora_esame' => '17:00',
            'prenotabile' => 0
        ];
        
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult->method('fetch_assoc')->willReturnOnConsecutiveCalls($expected,null);

        $mockQuery = $this->createMock(mysqli_stmt::class);
        $mockQuery->method('bind_param')->willReturn(true);
        $mockQuery->method('execute')->willReturn(true);
        $mockQuery->method('get_result')->willReturn($mockResult);
        $mockQuery->method('close')->willReturn(true);
        
        $mockConn = $this->createMock(mysqli::class);
        $mockConn->expects($this->once())
                 ->method('prepare')
                 ->willReturn($mockQuery);

        $ID = 'D00';
        $errorMessage = '';

        $result = getEsami($mockConn, $ID, $errorMessage);

        $this->assertNotNull($result);
        $this->assertInstanceOf(mysqli_result::class, $result);

        $dati = $result->fetch_assoc();

        $this->assertIsArray($dati);
        $this->assertArrayHasKey('id', $dati);
        $this->assertArrayHasKey('nome_esame', $dati);
        $this->assertArrayHasKey('data_esame', $dati);
        $this->assertArrayHasKey('ora_esame', $dati);
        $this->assertArrayHasKey('prenotabile', $dati);

        $this->assertNotEmpty($dati['id']);

        $this->assertEquals($expected, $dati);
        $this->assertEquals('', $errorMessage);        

    }

    function testGetEsamiExecuteFail(){

        $mockQuery = $this->createMock(mysqli_stmt::class);
        $mockQuery->method('bind_param')->willReturn(true);
        $mockQuery->method('execute')->willReturn(false);

        $mockConn = $this->createMock(mysqli::class);
        $mockConn->expects($this->once())
                 ->method('prepare')
                 ->willReturn($mockQuery);

        $ID = 'D00';
        $errorMessage = '';
        $result = getEsami($mockConn, $ID, $errorMessage);

        $this->assertNull($result);
        $this->assertNotEmpty($errorMessage);
    }

    function testGetCategorieEsami(){
        $expected = [
            'tuttiEsami' => 0,
            'prossimi' => 0,
            'completati' => 0
        ];

        $esami = [
            'id' => '15',
            'nome_esame' => 'SWBD',
            'data_esame' => '15/02/2025',
            'ora_esame' => '17:00',
            'prenotabile' => 0,
            'completato' => 1,
            'tipologia' => 'scritto',
            'corso' => '15'
        ];

        $mockExam = $this->createMock(mysqli_result::class);
        $mockExam->method('fetch_assoc')->wilLReturnOnConsecutiveCalls($esami, null);

        $iscritti = [
            'numero_iscritti' => 20
        ];

        $stats = [
            'media_voti' => 25,
            'ammessi' => 15
        ];
        
        $mockResultIscritti = $this->createMock(mysqli_result::class);
        $mockResultIscritti->method('fetch_assoc')->willReturnOnConsecutiveCalls($iscritti, null);

        $mockQueryIscritti = $this->createMock(mysqli_stmt::class);
        $mockQueryIscritti->method('bind_param')->willReturn(true);
        $mockQueryIscritti->method('execute')->willReturn(true);
        $mockQueryIscritti->method('get_result')->willReturn($mockResultIscritti);
        $mockQueryIscritti->method('close')->willReturn(true);
        

        $mockResultStats = $this->createMock(mysqli_result::class);
        $mockResultStats->method('fetch_assoc')->willReturnOnConsecutiveCalls($stats, null);

        $mockQueryStats = $this->createMock(mysqli_stmt::class);
        $mockQueryStats->method('bind_param')->willReturn(true);
        $mockQueryStats->method('execute')->willReturn(true);
        $mockQueryStats->method('get_result')->willReturn($mockResultStats);
        $mockQueryStats->method('close')->willReturn(true);

        $mockConn = $this->createMock(mysqli::class);
        $mockConn->method('prepare')->willReturnOnConsecutiveCalls($mockQueryIscritti, $mockQueryStats);

       

        $errorMessage = '';

        $result = getCategorieEsami($mockConn, $mockExam, $errorMessage);

        $this->assertNotNull($result);
        $this->assertIsArray($result);

        $this->assertArrayHasKey('tuttiEsami', $result);
        $this->assertArrayHasKey('prossimi', $result);
        $this->assertArrayHasKey('completati', $result);

        $this->assertCount(1, $result['tuttiEsami']);
        $this->assertEquals($iscritti['numero_iscritti'], $result['tuttiEsami'][0]['num_iscritti']);
        $this->assertEquals($stats['media_voti'], $result['completati'][0]['media_voti']);
        $this->assertEquals($stats['ammessi'], $result['completati'][0]['partecipanti']);

        $this->assertEmpty($errorMessage);


    }







}
?>