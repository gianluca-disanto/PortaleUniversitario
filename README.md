# Sistema di gestione degli esami universitari

Il progetto è stato sviluppato come parte di un’attività accademica e ha come finalità l’apprendimento pratico di principi legati all’ingegneria del software, alla modellazione dei requisiti, alla progettazione di databases e allo sviluppo web.
Eventuali evoluzioni future del sistema potrebbero allinearsi a esigenze più ampie di digitalizzazione dei processi universitari.

Questo documento fornisce le istruzioni dettagliate per configurare l'ambiente di sviluppo, installare le dipendenze e lanciare i test di unità del progetto.

---

### 1. PREREQUISITI E CONFIGURAZIONE PHP

Assicurarsi di avere installato i seguenti software prima di procedere:

* **XAMPP**
* **Composer** : Il gestore di dipendenze per PHP.

**Configurazione dell'estensione PHP Zip (necessario per l'utilizzo del Composer):**

1.  Aprire il file `php.ini` di PHP.
    * **Per XAMPP**: si trova tipicamente in `C:\xampp\php\php.ini`. 
2.  Cercare la riga `;extension=zip`.
3.  **Rimuovere il punto e virgola** all'inizio della riga in modo che diventi:
    ```
    extension=zip
    ```
4.  Salvare il file `php.ini`.
5.  **Riavviare il server Apache** affinché le modifiche abbiano effetto.


---

### 2. CONFIGURAZIONE DEL DATABASE

1.  **Avvia Apache e MySQL**:
    Se si utilizza XAMPP, avviare i servizi di Apache e MySQL dal pannello di controllo.

2.  **Accedi a phpMyAdmin**:
    Aprire il browser web all'indirizzo `http://localhost/phpmyadmin`

3.  **Crea un Nuovo Database**:
    * Nella colonna di sinistra di phpMyAdmin, cliccare su **"Nuovo database"**.
    * Nel campo "Nome database", inserire un nome per il database, ad esempio `gestione_esami`.
    * Cliccare su **"Crea"**.

4.  **Importa il Dump Completo del Database**:
    * Nella colonna di sinistra di phpMyAdmin, selezionare il database appena creato (es. `gestione_esami`).
    * Nella barra dei menu in alto, cliccare sulla scheda **"Importa"**.
    * Nella sezione "File da importare", cliccare su **"Scegli file"** e selezionare il file `database/dump_gestione_esami.sql` dalla directory del progetto.
    * "Formato" deve essere impostato su **"SQL"**.
    * Cliccare su **"Esegui"** per avviare l'importazione.
    * Una volta completata l'operazione, si avrà il dump importato.

5.  **Configurazione delle Credenziali PHP**:
    Aprire il file `config/db_connection.php`.
    Modificare le seguenti linee con le credenziali del database. L'utente predefinito per MySQL è `root` senza password (o con una password vuota).
    ```php
    // config/db_connection.php
    $host = "localhost";        
    $user = "root";            
    $password = "";             
    $database = "gestione_esami"; // Deve corrispondere al nome assegnato sopra es. `gestione_esami`
    ```
    Salvare il file.

---


### 3. INSTALLAZIONE DELLE DIPENDENZE PHP (CON COMPOSER)

1.  **Navigare nella directory del progetto**:
    Aprire il terminale o il prompt dei comandi e navigare nella directory principale del progetto (dove si trova il file `composer.json`).
    ``` bash
    \PortaleUniversitario\
    ```

2.  **Installare PHPUnit**:
    Eseguire il seguente comando per installare PHPUnit e altre dipendenze definite in `composer.json`.
    ```bash
    composer install
    ```
    Se `composer.json` non è presente o non include PHPUnit e FPDF, è possibile aggiungerlo come dipendenza di sviluppo. Creare `composer.json` e incollare:
    ```json
    {
        "autoload": {
            "files": [
                "src/esami/esamiController.php",
                "src/profilo/profiloController.php",
                "src/statistiche/statisticheController.php"
            ]
        },
        "autoload-dev": {
            "psr-4": {
                "Tests\\": "tests/"
            }
        },
        "require": {
                "setasign/fpdf": "^1.8"
            },
        "require-dev": {
            "phpunit/phpunit": "^11.5"
        }
    }

    ```
    Dopo aver modificato `composer.json`, esegui `composer install` o `composer update`. Questo creerà la cartella `vendor/` e scaricherà tutte le librerie necessarie.

---

### 4. CONFIGURAZIONE DEL SERVER WEB (APACHE)

1.  **Spostare il Progetto**:
    Copiare l'intera cartella del progetto nella directory `htdocs` di Apache (per XAMPP: `C:\xampp\htdocs\`), assicurandosi di rinominare la cartella di progetto in `PortaleUniversitario`.

2.  **Avviare Apache**:
    Dopo le modifiche, avviare il server Apache.

---

### 5. LANCIO DEI TEST DI UNITÀ (PHPUNIT)

1.  **Navigare nella directory del progetto**:
    Assicurarsi di essere nella directory principale del progetto tramite terminale/prompt dei comandi.

2.  **Eseguire i test**:
    PHPUnit viene eseguito tramite lo script eseguibile che Composer ha scaricato nella cartella `vendor\bin`.
    ```bash
    .\vendor\bin\phpunit
    ```
    Esempio di esecuzione di un singolo test file:
    ```bash
    .\vendor\bin\phpunit tests\EsamiTest.php
    ```

---

### 6. ACCESSO ALL'APPLICAZIONE WEB

Dopo aver configurato il server web, è possibile accedere all'applicazione tramite il browser:

* Dopo aver copiato direttamente in `htdocs`:
    Aprire il browser e andare a `http://localhost/PortaleUniversitario/`

---

### 7. CREDENZIALI DI ACCESSO DI PROVA
Email: 
```
accessoprova@university.it
```
Password (DEFAULT):
```
Password123!
```
---