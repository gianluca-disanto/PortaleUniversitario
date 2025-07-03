<?php

use PHPUnit\Framework\TestCase;



class ProfiloTest extends TestCase {

    public function testGetDatiDocente() {

        $expected = [
            'nome' => 'Tizio',
            'cognome' => 'Caio',
            'matricola' => 'D0001',
            'email' => 'tiziocaio@esempio.com',
            'cf' => 'CF9293GS1SKDKSIW'
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


        // Richiamo la funzione da testare
        $ID = 'D0001';
        $message = ''; 

        $result = getDatiDocente($mockConn, $ID, $message);

        $this->assertNotNull($result);
        $this->assertInstanceOf(mysqli_result::class, $result); // Verifica che sia un oggetto mysqli_result 

        // la funzione getDatiDocente(..) ritorna un get_result() --> quindi un oggetto mysqli_result. Bisogna fare un fetch per raggiungere i dati nella forma aspettata
        $dati = $result->fetch_assoc();

        // Asserzioni sui dati estratti
        $this->assertIsArray($dati);
        $this->assertArrayHasKey('nome', $dati);
        $this->assertArrayHasKey('cognome', $dati);
        $this->assertArrayHasKey('matricola', $dati);
        $this->assertArrayHasKey('email', $dati);
        $this->assertArrayHasKey('cf', $dati);

        $this->assertEquals($expected, $dati);
        $this->assertEquals('', $message);
    }

    function testDatiDocenteAssenti(){
        $expected = null;
    
        $mockQuery = $this->createMock(mysqli_stmt::class);
        $mockQuery->method('bind_param')->willReturn(true);
        $mockQuery->method('execute')->willReturn(false);
        $mockQuery->method('close')->willReturn(true);
       

        $mockConn = $this->createMock(mysqli::class);
        $mockConn->expects($this->once())
                 ->method('prepare')
                 ->willReturn($mockQuery);

        $ID = 'D0001';
        $message = '';
        $result = getDatiDocente($mockConn, $ID, $message);

        $this->assertEquals($expected, $result);
        $this->assertNotEquals('', $message);
    }

    function testStatsDocente(){
        $expected = [
            'corsi_totali' => 1,
            'esami_totali' => 2,
            'esami_completati' => 0
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

       
        

        $ID = 'D0001';
        $message = '';
        $result = getStatsDocente($mockConn, $ID, $message);
        $dati = $result->fetch_assoc();
        

        $this->assertIsArray($dati);
        $this->assertArrayHasKey('corsi_totali', $dati);
        $this->assertArrayHasKey('esami_totali', $dati);
        $this->assertArrayHasKey('esami_completati', $dati);

        $this->assertEquals($expected, $dati);
        $this->assertEquals('',$message);
    }
}