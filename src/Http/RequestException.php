<?php

namespace Xin\Http;

class RequestException extends HttpClientException
{

	/**
	 * The response instance.
	 *
	 * @var Response
	 */
	public $response;

	/**
	 * Create a new exception instance.
	 *
	 * @param Response $response
	 * @return void
	 */
	public function __construct(Response $response)
	{
		parent::__construct($this->prepareMessage($response), $response->status());

		$this->response = $response;
	}

	/**
	 * Prepare the exception message.
	 *
	 * @param Response $response
	 * @return string
	 */
	protected function prepareMessage(Response $response)
	{
		$message = "HTTP request returned status code {$response->status()}";

		$summary = class_exists(\GuzzleHttp\Psr7\Message::class)
			? \GuzzleHttp\Psr7\Message::bodySummary($response->toPsrResponse())
			: \GuzzleHttp\Psr7\get_message_body_summary($response->toPsrResponse());

		return is_null($summary) ? $message : $message .= ":\n{$summary}\n";
	}

}
