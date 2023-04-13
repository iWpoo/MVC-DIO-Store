<?php

namespace App\Core;
	
class Router	
{
	public function getTrack($routes, $uri)
	{
		foreach ($routes as $route) {
			$pattern = $this->createPattern($route->path); // см. описание метода
			
			/*
				Проверяем адрес URI на соответствие регулярке
				Если URI подойдет под регулярку, в $params будут параметры
			*/
			if (preg_match($pattern, $uri, $params)) {
				$params = $this->clearParams($params);  // очищаем параметры от элементов с числовыми ключами
				return new Track($route->controller, $route->action, $params);	
			}
		}
			
		return new Track('error', 'notFound');
	}
		
	private function createPattern($path)
	{
		return '#^' . preg_replace('#/:([^/]+)#', '/(?<$1>[^/]+)', $path) . '/?$#';
	}
	
		
	private function clearParams($params)
	{
		$result = [];
		
		foreach ($params as $key => $param) {
			if (!is_int($key)) {
				$result[$key] = $param;
			}
		}
			
		return $result;
	}	
}

