<?php
class Af_Lackadaisy extends Plugin {

	private $host;

	function about() {
		return array(1.0,
			"Display Lackadaisy comics and news text in articles",
			"Will Hughes");
	}

	function init($host) {
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
	}

	function hook_article_filter($article) {
		$owner_uid = $article["owner_uid"];

		if (strpos($article["link"], "lackadaisy.foxprints.com") !== FALSE) {
			if (strpos($article["plugin_data"], "lackadaisy,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				@$doc->loadHTML(fetch_file_contents($article["link"]));

				$basenode = false;

				if ($doc) {
					$xpath = new DOMXPath($doc);
					$entries = $xpath->query('(//img[@src])');

					$matches = array();

					foreach ($entries as $entry) {

						if (preg_match("/(.*\/(comic|gallery)\/.*)/i", $entry->getAttribute("src"), $matches)) {
							$basenode = $entry;
							break;
						}
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode);

						$entries = $xpath->query('(//div[@class="description")');
						
						for ($entries as $entry) {
							$article["content"] = $article["content"] . "<br />" . $doc->saveXML($entry);
						}

						$article["plugin_data"] = "lackadaisy,$owner_uid:" . $article["plugin_data"];
					}
				}
			} else if (isset($article["stored"]["content"])) {
				$article["content"] = $article["stored"]["content"];
			}
		}

		return $article;
	}

	function api_version() {
		return 2;
	}
}
?>