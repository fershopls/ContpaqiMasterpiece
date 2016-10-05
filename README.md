# Documentación ContpaqiMasterpiece

ContpaqiMasterpiece es un mini-framework escrito en PHP que facilita el acceso a diferentes bases de datos provenientes de Contpaqi.

Su función básica es realizar una misma consulta _(Query Statement)_ en una lista de bases de datos que contengan la misma estructura interna y devolver resultados _(rows)_ agrupados por base de datos.

Los dos pilares que sostienen el framework son las clases ```Query``` y ```Manager```.

## Query
Toda clase tipo query se extiende de ```lib\Database\Query\QueryInterface``` y contiene dos métodos abstractos que deben ser definidos: ```getQuery()``` y ```handle()```.
Además existe la opción de definir el atributo ```$queryFetchMode``` que [configura el fetch_style](http://php.net/manual/es/pdostatement.fetch.php#fetch_style) del PDO.

#### ```getQuery ()```
Devuelve una consulta *(Query Statement)* a ser ejecutada en cada base de datos.

#### ```handle ($query_object)```
Se ejecuta automáticamente cuando el método ```$query->execute()``` finaliza. Tiene cómo parametro único ```$queryObject``` que no es más que un array bidimensional con la estructura: ```[$database => $query_result, ...]```. Se encarga de la lógica y el ordenamiento de las filas resultantes del query y devuelve el resultado que, a su vez ```$query->execute()``` devolverá.


### Ejemplo de Uso
__Definir clase de Query__
```php
class MyQuery extends lib\Database\Query\QueryInterface {

    protected $queryFetchMode = \PDO::FETCH_ASSOC;

    public function getQuery ()
    {
        return "SELECT * FROM users WHERE group_id = :group_id;";
    }

    public function handle ($query_object)
    {
        $result = array();
        foreach ($query_object as $db_slug => $rows)
        {
            foreach ($rows as $row)
            {
                $result[$db_slug][$row['id']] = $row;
            }
        }
        return $result;
    }
}
```

__Usar Query__
```php
$parameters = array("group_id" => 13);
$myQuery = $this->query(MyQuery::class);
$myq_rows = $myQuery->execute($parameters);
```

__Resultado:__
```php
$myq_rows = array(
    "database_1" => [
        1 => ["id"=>1, "nick"=>"smith", "group_id"=>13],
        2 => ["id"=>2, "nick"=>"inger", "group_id"=>13],
    ],
    "database_2" => [
        5 => ["id"=>5, "nick"=>"john", "group_id"=>13],
        7 => ["id"=>7, "nick"=>"swan", "group_id"=>13],
    ]
);
```

## Manager
Se encarga de administrar consultas e implementar mayor lógica en los resultados de las mismas para devolver un objeto más complejo. Soporta diferentes querys.
Se extiende de ```lib\Reporter\ReporterInterface``` y a diferencia de ```QueryInterface``` no contiene ningún método abstracto sino que hereda ```Métodos de Inyección``` y ```Métodos de Implementación``` además de ```createCsv()```.

### Métodos de Inyección
Cada método de inyección asigna parametros específicos dentro de la clase.
-  ```injectPdo ($classPdo)```
    Asigna la instancia de PDO que se utilizará dentro de la clase.
    Por defecto se asignará una instancia de ```lib\Database\StackPDO``` autentificada.
- ```injectDbs ($arrayDbs) ```
    El argumento ```$arrayDbs``` debe contener los nombres de cada **base de datos existente** a utilizar.
- ```injectParameters ($arrayParameters)```
    Su función es mantener el correcto acceso a ```$this->parameters```.

### Métodos de Implementación
Se encargan de instanciar y mantener sincronizadas las clases tipo ```Query``` y ```Manager```. Inyectan automáticamente todos los parámetros de la clase madre, a no ser que se especifique lo contrario.

**Realizar una implementación:**
 - ```query ($classQuery, $databases = null, $pdo = null)```
 - ```import ($classManager, $databases = null, $pdo = null, $parameters = null)```

Antes de llamar cualquier método de implementación debe haberse importado previamente una clase del tipo ```Query``` o ```Manager``` con la keyword ```use```:
 ```php
 use MyApp\Querys\MyQuery;
 ```
 Ya que los métodos de implementación se encargarán por sí mismos de instanciarla e inyectarle los parámetros correspondientes, el argumento ```$classQuery``` **no debe ser una clase instanciada** sino, la constante ```class``` de la misma, se obtiene de la siguiente manera: ```MyClass::class```.

El argumento ```$databases``` debe ser un array que contenga en cada elemento una base de datos existente. En caso de ser nulo se inyectarán las bases de datos de la clase madre.
```php
$databases = array("Database1", "Database2", "Database3");
```

El argumento ```$pdo``` debe ser una clase instanciada de ```lib\Database\StackPDO``` autentificada y lista para usar. En caso de ser nulo se inyectarán las bases de datos de la clase madre.
```php
use lib\Database\StackPDO;

$pdo = new StackPDO('your_hosting', 'your_username', 'your_password');
```

El último argumento ```$parameters```, **debe ser declarado únicamente al llamar el método ```import```**. Son los parámetros que se inyectarán en el ```Manager```. En caso de ser nulo se inyectará la variable ```$this->parameters```.

**Ejemplo de uso:**
 ```php
 use MyApp\Query\MyQuery;
 use MyApp\Manager\MyManager;
 use lib\Database\StackPDO;

$databases = array("Database1", "Database2", "Database3");
$pdo = new StackPDO('your_hosting', 'your_username', 'your_password');
$bind_values = array("group_id" => 13);

 $query_result = $this->query(MyQuery::class)
    /* Podemos sobreescribir los parámetros inyectados usando
    directamente el método de inyección una vez instanciada la clase. */
    ->injectDbs(['uniqDb1', 'uniqDb2'])
    /* Podemos pasar valores de blindaje que serán añadidos
    como argumentos al método execute() para hacer más dinámica
    la consulta */
    ->execute($bind_values);

/* Manager funciona de la misma manera, con la exepción de
que los parámetros son inyectados */
$manager = $this->import(MyManager::class)
    ->injectParameters(['otherParams'=>321]);
/* Se tiene acceso a todos los métodos,
se pueden enviar también argumentos */
$stuff = $manager->doStuff('Arg1', 2);
 ```
[Más información del blindaje](http://php.net/manual/es/pdostatement.execute.php)


#### CSV
Además de los métodos de inyección e implementación contamos con el método ```createCsv($csv_headers, $csv_rows, $csv_fix_top = [])```.

```$csv_headers``` Más que sólo contener headers este ```array``` marca la pauta y el orden en que serán escritas las columnas de cada fila.
```php
$csv_headers = array(
    "title" => "Título",
    "content" => "Contenido",
    "date" => "Fecha"
);
```

```$csv_rows``` es un ```array``` que agrupa cada fila a ser insertada en csv.

```php
$csv_row = array(
    ["title"=>"Título1", "content"=>"123", "date"=>"20160403"],
    ["content"=>"456", "date"=>"20160801", "title"=>"Título2"],
    ["title"=>"Título2", "date"=>"20160801", "content"=>"456", "extra" => "1"],
);
```

```$csv_fix_top``` es sólo un ```array``` que se inserta al comienzo del archivo en caso de que se requiera un header de más de una fila.

```return``` Esta función regresa un string csv listo para escribir en un archivo.

En este caso el resultado sería:

```csv
"Título", "Contenido", "Fecha"
"Título1", "123", "20160403"
"Título2", "456", "20160801"
"Título2", "456", "20160801"
```
Sólo se imprimen los valores cuya clave también se encuentra **(cómo clave)** en ```$csv_headers```. Los valores se imprimen en el mismo orden que fueron registrados en ```$csv_headers```.