<?php 
	require 'vendor/autoload.php';

	//configuração de conexão com banco de dados
	$pdo = new PDO('mysql:dbname=slim_helloworld;host:127.0.0.1','root','');
	$db	 = new NotORM($pdo);
	
	//criando uma nova instancia do Slim
	$app = new \Slim\Slim(array(
		'MODE' => 'development',
		'TEMPLATE.PATH' => './templates'
	));

	//$app->contentType('text/html; charset=utf-8');


	//criando uma nova Rota
	$app->get('/', function() use($app){
		$app->render('index.php');
		//echo "Hello Slim World";
	});


	//obtendo todos os livros
	$app->get('/books', function() use ($app, $db){
		$books = array();
		foreach ($db->books() as $book) {
			$books[] = array(
				'id' => $book['id'],
				'title' => $book['title'],
				'author' => $book['author'],
				'summary' => utf8_encode($book['summary'])
			);
		}
		
		$app->response()->header('Content-Type', 'application/json; charset=UTF-8');
		echo json_encode($books);
		//print_r($books);
	});


	//obtendo um livro pelo id
	$app->get('/books/:id', function($id) use ($app,$db){
		//cabeçalho da resposta
		$app->response()->header('Content-Type', 'aplication/json');
		//setando o id do livro para busca
		//$book = $db->books()->where('id', $id);
		$book = $db->books('id = ?', $id);

		if($data = $book->fetch()){
			echo json_encode(array(
				'id' => $data['id'],
				'title' => $data['title'],
				'author' => $data['author'],
				'summary' => $data['summary']
			));
		}else{
			echo json_encode(array(
				'status' => false,
				'message' => 'Book ID $id does not existe'
			));
		}
	});


	//adicionando um novo livro
	$app->post('/book', function() use ($app, $db){
		$app->response()->header('Content-Type', 'application/json');
		$book = utf8_encode($app->request()->post());
		$result = $db->books->insert($book);
		echo json_enconde(array('id'=>$result['id']));
	});


	//editanto um livro
	$app->put('/books/:id', function($id) use($app, $db){
		$app->response()->header('Content-Type', 'application/json');
		$book = $db->books()->where('id',$id);
		if($book->fetch()){
			$post = $app->request->put();
			$result = $book->update($post);
			echo json_encode(array(
				'status' => (bool)$result,
				'message' => 'Book updated successfully'
			));
		}else{
			echo json_encode(array(
				'status' => false,
				'message' => 'Book id $id does not exist'
			));
		}
	});

	//rodando a aplicação
	$app->run();

 ?>