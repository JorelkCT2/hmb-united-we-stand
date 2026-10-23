<?php
namespace Joomla\Component\Efemerides\Administrator\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;

class BandasController extends AdminController
{
    protected $text_prefix = 'COM_EFEMERIDES_BANDA';
    public function getModel($n='Banda',$p='Administrator',$c=['ignore_request'=>true]) { return parent::getModel($n,$p,$c); }

    public function importCsv(): void
    {
        $this->checkToken();
        $app  = Factory::getApplication();
        $file = $this->input->files->get('csv_bandas',null,'raw');
        if (empty($file)||$file['error']!==UPLOAD_ERR_OK||$file['size']===0) {
            $app->enqueueMessage('Archivo CSV no válido.','error');
            $this->setRedirect(Route::_('index.php?option=com_efemerides&view=bandas',false)); return;
        }
        $db=$this->parseAndImport($file['tmp_name'],'bandas');
        $this->setRedirect(Route::_('index.php?option=com_efemerides&view=bandas',false));
    }

    private function parseAndImport(string $tmp, string $type): void
    {
        $app=$this->app; $db=Factory::getContainer()->get('DatabaseDriver');
        $handle=fopen($tmp,'r');
        $first=fgets($handle); rewind($handle);
        $sep=substr_count($first,';')>substr_count($first,',') ?';':',';
        $header=fgetcsv($handle,0,$sep);
        if(!$header){$app->enqueueMessage('CSV sin cabecera.','error');fclose($handle);return;}
        $header=array_map(static fn($h)=>mb_strtolower(trim($h)),$header);
        $ok=$skip=0; $now=(new \DateTime())->format('Y-m-d H:i:s');

        while(($row=fgetcsv($handle,0,$sep))!==false){
            if(count($row)<1){$skip++;continue;}
            $d=array_combine($header,array_pad($row,count($header),''));
            $nombre=trim($d['nombre']??($d['banda']??($d['name']??'')));
            if(!$nombre){$skip++;continue;}
            $obj=(object)[
                'nombre'           =>$nombre,
                'wikipedia_url'    =>trim($d['wikipedia_url']??($d['wikipedia']??''))?:null,
                'foto'             =>trim($d['foto']??($d['photo']??''))?:null,
                'fecha_formacion'  =>$this->pd(trim($d['fecha_formacion']??($d['formacion']??''))),
                'fecha_disolucion' =>$this->pd(trim($d['fecha_disolucion']??($d['disolucion']??''))),
                'pais'             =>trim($d['pais']??($d['country']??''))?:null,
                'genero'           =>trim($d['genero']??($d['genre']??''))?:null,
                'descripcion'      =>trim($d['descripcion']??'')?:null,
                'state'=>1,'created'=>$now,'modified'=>$now,
            ];
            try{
                $db->setQuery('INSERT IGNORE INTO #__efemerides_bandas (nombre,wikipedia_url,foto,fecha_formacion,fecha_disolucion,pais,genero,descripcion,state,created,modified) VALUES ('
                    .implode(',',[$db->quote($obj->nombre),$obj->wikipedia_url?$db->quote($obj->wikipedia_url):'NULL',
                    $obj->foto?$db->quote($obj->foto):'NULL',$obj->fecha_formacion?$db->quote($obj->fecha_formacion):'NULL',
                    $obj->fecha_disolucion?$db->quote($obj->fecha_disolucion):'NULL',
                    $obj->pais?$db->quote($obj->pais):'NULL',$obj->genero?$db->quote($obj->genero):'NULL',
                    $obj->descripcion?$db->quote($obj->descripcion):'NULL','1',$db->quote($now),$db->quote($now)]).
                    ') ON DUPLICATE KEY UPDATE wikipedia_url=IF(VALUES(wikipedia_url) IS NOT NULL,VALUES(wikipedia_url),wikipedia_url),foto=IF(VALUES(foto) IS NOT NULL,VALUES(foto),foto),modified='.$db->quote($now)
                )->execute(); $ok++;
            }catch(\Exception $e){$skip++;}
        }
        fclose($handle);
        $app->enqueueMessage("{$ok} bandas importadas/actualizadas. {$skip} omitidas.",'success');
    }

    private function pd(string $d): ?string
    {
        if(!$d||$d==='0000-00-00') return null;
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$d)) return $d;
        if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/',$d,$m)) return sprintf('%04d-%02d-%02d',$m[3],$m[2],$m[1]);
        if(preg_match('/^\d{4}$/',$d)) return $d.'-01-01';
        return null;
    }
}
