<?
namespace Whoyasha\Teenyicons;
	
class Icon
{
	private static $instance = null;

	private static $icons = [];
	private static $type = "outline";

	const HIDDEN_STYLE = "style=\"display:none;\"";
	const HIDDEN_CLASS = "class=\"visualy-hidden\"";

	public function Set(&$content) : string
	{
		$content = static::GetIcons($content);
		$content = static::testContent($content);
		
		return $content;
	}
	
	protected function testContent($content)
	{
		$content = str_replace("TEST_CONTENT", "TEST_CONTENT_REPLACED", $content);
		
		return $content;
	}
	
	protected static function GetIcons($content)
	{
		preg_match_all("/{{?([a-z\-]+)}?(([0-9]+),([0-9]+)}|(([0-9]+)})|()})/", $content, $matches);
		
		$params = array_map('static::PrepParams', $matches[2]);

		foreach($matches[0] as $id => $icon) {
			
			if($matches[1][$id]) {
				static::$icons[] = [
					"ICON" => $matches[1][$id],
					"SIZE" => $params[$id][0] == 0 ? "100%" : $params[$id][0],
					"BOTH" => $params[$id][1] == 0 ? false : true
				];
			}
		}
		
		$content = str_replace($matches[0], static::GetSvgFromFiles(), $content);
		
		return $content;
	}
	
	protected static function PrepParams($icon)
	{
		$parse = explode(",", str_replace("}", "", $icon));
		$parse = array_map('trim', $parse);
		
		return [
			(int) $parse[0],
			(int) $parse[1]
		];
	}
	
	protected static function GetSvgFromFiles() {
		
		foreach(static::$icons as $id => $icon)
		{
			$path = ["MAIN" => __DIR__ . "/" . static::$type  . "/"];
			
			if($icon["BOTH"]) {
				$tooggle_type = static::$type == "outline" ? "solid" : "outline";
				$path["TOGGLE"] = __DIR__ . "/" . $tooggle_type  . "/";
			}
			
			foreach($path as $code => $item)
			{
				if($svg = static::GetFilePath($item, $icon["ICON"]))
				{
					if($code == "TOGGLE")
						$svg = str_replace("<svg", "<svg " . static::HIDDEN_STYLE, $svg);

					$svgs[$id][] = str_replace(
						[
							"stroke=\"black\"",
							"fill=\"black\"",
							"width=\"15\" height=\"15\"",
							"  "
						],
						[
							"stroke=\"currentColor\"",
							"fill=\"currentColor\"", 
							"width=\"{$icon["SIZE"]}\" height=\"{$icon["SIZE"]}\"",
							" "
						], 
						$svg
					);
	
				} 
				else 
				{
					$svgs[$id] = null;
				}
			}
			
			if($svgs[$id])
				$svgs[$id] = implode('', $svgs[$id]);
		}
		
		return $svgs;
	}
	
	protected static function GetFilePath($path, $icon) : string
	{
		$result = $path. $icon . ".svg";
		
		if(file_exists($result))
			return file_get_contents($result);
			
		return false;
	}
}
