<?php

use PHPUnit\Framework\TestCase;



class StatisticheTest extends TestCase{
    function testGetEsamiProgrammati(){
        $expected = [
            'id' => '11',
            'nome_esame' => 'SWBD',
            'data_esame' => '2025/07/15',
            'ora_esame' => '14:00',
            'prenotabile' => 1            
        ];

        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult->method('fetch_assoc')->willReturnOnConsecutiveCalls($expected, null);


        $mockQuery = $this->createMock(mysqli_stmt::class);
        $mockQuery->method('bind_param')->willReturn(true);
        $mockQuery->method('execute')->willReturn(true);
        $mockQuery->method('close')->willReturn(true);
        // Quando get_result viene chiamato su $mockQuery, restituisce il mock del risultato
        $mockQuery->method('get_result')->willReturn($mockResult);

        $mockConn = $this->createMock(mysqli::class);
        $mockConn->expects($this->once())
                 ->method('prepare')
                 ->willReturn($mockQuery); 
        $mockConn->method('close')->willReturn(true); 

        $ID = 'D004';
        $errorMessage = '';
        $result = getEsamiProgrammati($mockConn, $ID, $errorMessage);

        $this->assertNotNull($result);
        $this->assertInstanceOf(mysqli_result::class, $result);

        $dati = $result->fetch_assoc();

        $this->assertIsArray($dati);
        $this->assertArrayHasKey('id', $dati);
        $this->assertArrayHasKey('nome_esame', $dati);
        $this->assertArrayHasKey('data_esame', $dati);
        $this->assertArrayHasKey('ora_esame', $dati);
        $this->assertArrayHasKey('prenotabile', $dati);

        $this->assertEquals($expected ,$dati);
        $this->assertEquals('', $errorMessage);
    }

    function testGetCorsi(){
        $expected = [
            'id' => '1'
        ];
    
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult->method('fetch_assoc')->willReturnOnConsecutiveCalls($expected, null);
    
    
        $mockQuery = $this->createMock(mysqli_stmt::class);
        $mockQuery->method('bind_param')->willReturn(true);
        $mockQuery->method('execute')->willReturn(true);
        $mockQuery->method('close')->willReturn(true);
        $mockQuery->method('get_result')->willReturn($mockResult);
    
        $mockConn = $this->createMock(mysqli::class);
        $mockConn->expects($this->once())
                    ->method('prepare')
                    ->willReturn($mockQuery); 
        $mockConn->method('close')->willReturn(true); 
    
        $ID = 'A1300';
        $errorMessage = '';
    
        $result = getCorsi($mockConn, $ID, $errorMessage);
    
        $this->assertNotNull($result);
        $this->assertInstanceOf(mysqli_result::class,$result);
    
        $dati = $result->fetch_assoc();
    
        $this->assertIsArray($dati);
        $this->assertArrayHasKey('id',$dati);
    
        $this->assertEquals($expected, $dati);
        $this->assertEquals('', $errorMessage);
    }
    
    function testGetDistribuzioneVotiQUeryFault(){
        $expected = null;
    
        $mockConn = $this->createMock(mysqli::class);
        $mockConn->expects($this->once())
                    ->method('prepare')
                    ->willReturn(false); 
    
        $ID = 'B14';
        $errorMessage = '';
    
        $result = getDistribuzioneVoti($mockConn, $ID, $errorMessage);
    
        $this->assertNull($result);
        $this->assertNotEmpty($errorMessage);
    }
}



?>