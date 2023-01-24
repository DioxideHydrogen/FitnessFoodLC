<?php

namespace App\Http\Controllers;

use App\Models\CronJob;
use App\Models\CronJobTask;
use App\Models\Product;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CronJobController extends Controller
{
    
	public function updateProducts() {

		\set_time_limit(3000); // 50min
		
		\ini_set('memory_limit', '2G');

		$cronJob = $this->registerCronJob();

		$jsonFiles = $this->getProductsJsonFileNamesToUpdate();

		$this->downloadFilesTempDir($jsonFiles);

		$this->descompactFiles($jsonFiles);

		$this->readJsonAndRegisterProductsInDatabase($jsonFiles, $cronJob);

		$this->deleteTempFiles($jsonFiles);

		$this->setEndTimeCronJob($cronJob);

	}

	public function registerCronJob() {
		
		$cronJob = new CronJob();

		$cronJob->started_at = date('Y-m-d H:i:s');

		$cronJob->save();

		return $cronJob;
	}

	public function getProductsJsonFileNamesToUpdate() {

		$url = 'https://challenges.coode.sh/food/data/json/index.txt';

		$client = new Client([
			'verify' => false
		]);

		try {

			$request = $client->get($url);

			$content = $request->getBody()->getContents();

			$filenames = explode("\n", $content);

			$filenames = \array_filter($filenames, function($filename) {
				return $filename != "";
			});

			return $filenames;

		} catch (ClientException $e) {

			$response = $e->getResponse()->getBody()->getContents();

			Log::emergency("Ocorreu um erro interno da API ao tentar obter os nomes dos arquivos de atualização. Response: {$response}");

			return \response()->json(['error' => true, 'message' => 'Ocorreu um erro ao tentar obter os arquivos de atualizações'], Response::HTTP_INTERNAL_SERVER_ERROR);

		} catch (ServerException $e) {

			$response = $e->getResponse()->getBody()->getContents();

			Log::emergency("Ocorreu um do servidor alvo ao tentar obter os nomes dos arquivos de atualização. Response: {$response}");

			return \response()->json(['error' => true, 'message' => 'Ocorreu um erro ao tentar obter os arquivos de atualizações'], Response::HTTP_INTERNAL_SERVER_ERROR);

		}


	}	

	public function downloadFilesTempDir(array $jsonFiles) {

		$urlBase = "https://challenges.coode.sh/food/data/json/";

		if(!$jsonFiles) return \response()->json(['error' => true, 'message' => 'Nenhum arquivo encontrado para atualização']);

		foreach($jsonFiles as $jsonFile) {

			$tempDir = \storage_path('app/temp');

			if(!\is_dir($tempDir)) \mkdir($tempDir, 0777, true);

			$fileStream = \fopen($tempDir . '/' . $jsonFile, 'w');

			$client = new Client(['verify' => false]);
			
			try {
				
				$url = $urlBase . $jsonFile;

				$request = $client->get($url, ['sink' => $fileStream]);

			} catch (ClientException $e) {

				$response = $e->getResponse()->getBody()->getContents();
	
				Log::emergency("Ocorreu um erro interno da API ao tentar baixar o arquivo de atualização. Response: {$response}");
	
				return \response()->json(['error' => true, 'message' => 'Ocorreu um erro ao tentar baixar o arquivo de atualização'], Response::HTTP_INTERNAL_SERVER_ERROR);
	
			} catch (ServerException $e) {
	
				$response = $e->getResponse()->getBody()->getContents();
	
				Log::emergency("Ocorreu um do servidor alvo ao tentar baixar o arquivo de atualização. Response: {$response}");
	
				return \response()->json(['error' => true, 'message' => 'Ocorreu um erro ao tentar baixar o arquivo de atualização'], Response::HTTP_INTERNAL_SERVER_ERROR);
	
			}
	

		}

		
	}

	public function descompactFiles(array $jsonFiles) {
	
		foreach($jsonFiles as $jsonFile) {

			$fileName = \storage_path('app/temp/') . $jsonFile;

			$bufferSize = 4096; 

			$outFileName = str_replace('.gz', '', $fileName); 

			$file = gzopen($fileName, 'rb');

			$outFile = fopen($outFileName, 'wb'); 

			while (!gzeof($file)) fwrite($outFile, gzread($file, $bufferSize));

			fclose($outFile);

			gzclose($file);

		}
	}	

	public function readJsonAndRegisterProductsInDatabase(array $jsonFiles, CronJob $cronJob) {
		
		foreach($jsonFiles as $jsonFile) {
			
			$cron_job_taks = new CronJobTask();

			$cron_job_taks->file = $jsonFile;

			$cron_job_taks->status = CronJobTask::PROCESSING;

			$cron_job_taks->cron_job_id = $cronJob->id;

			$cron_job_taks->save();

			$jsonFile = \storage_path('app/temp/') . str_replace('.gz', '', $jsonFile); 

			$stream = \fopen($jsonFile, 'r');

			$line = \fgets($stream);

			$productsImported = 0;

			while($line !== false) {

				$json = \json_decode(\fgets($stream), true);

				try {
					
					$this->registerOrUpdateProduct($json);

					$cron_job_taks->status = CronJobTask::SUCCESS;

					$productsImported++;

					
				} catch (Exception $e) {

					$cron_job_taks->status = CronJobTask::ERROR;

					$cron_job_taks->message = $e->getMessage();

				}
				
				$cron_job_taks->update();

				if($productsImported === 100) break;
					

				
				
			}

			fclose($stream);

		}

	}

	public function registerOrUpdateProduct(array $productJson) {

		$update = true;

		$product = Product::where('code', $productJson['code'])->first();

		if(!$product) { $product = new Product(); $update = false; }

		$product->code = \preg_replace('/[^0-9]/','', $productJson['code']);

		$product->status = $update ? $product->status : 'draft';

		$product->url = $productJson['url'];
		
		$product->creator = $productJson['creator'];
		
		$product->name = $productJson['product_name'];
		
		$product->quantity = $productJson['quantity'];
		
		$product->brands = $productJson['brands'];
		
		$product->categories = $productJson['categories'];
		
		$product->labels = $productJson['labels'];
		
		$product->cities = $productJson['cities'];
		
		$product->purchase_places = $productJson['purchase_places'];
		
		$product->stores = $productJson['stores'];
		
		$product->ingredients = $productJson['ingredients_text'];
		
		$product->traces = $productJson['traces'];
		
		$product->serving_size = $productJson['serving_size'];
		
		$product->serving_quantity = $productJson['serving_quantity'];
		
		$product->nutriscore_score = $productJson['nutriscore_score'];
		
		$product->nutriscore_grade = $productJson['nutriscore_grade'];
		
		$product->main_category = $productJson['main_category'];
		
		$product->image_url = $productJson['image_url'];

		$product->imported_t = date('Y-m-d H:i:s');

		if($update) 
			$product->update();
		else 
			$product->save();

	}

	public function deleteTempFiles(array $jsonFiles) {

		if(!$jsonFiles) return \response()->json(['error' => true, 'message' => 'Nenhum arquivo encontrado para exclusão']);

		foreach($jsonFiles as $jsonFile) {

			$tempDir = \storage_path('app/temp/');

			$gzFile = $tempDir . $jsonFile;

			$jsonFile = $tempDir . \str_replace(".gz", "", $jsonFile);

			\unlink($gzFile);

			\unlink($jsonFile);

		}

	}

	public function setEndTimeCronJob(CronJob $cronJob) {

		$cronJob->ended_at = date("Y-m-d H:i:s");

		$cronJob->update();

	}
	

}
