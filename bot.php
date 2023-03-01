<?php
require_once 'comandos.php';

class TelegramBot {

    private $token;
    private $update;
    private $chatId;
    private $mensaje;
    private $website;

    public function __construct($token) {
        $this->token    = $token;
        $this->update   = json_decode(file_get_contents('php://input'), true);
        $this->chatId   = $this->update['message']['chat']['id'];
        $this->mensaje  = $this->update['message']['text'];
        $this->website  = 'https://api.telegram.org/bot'.$this->token;
    }

    public function sendMessage() {
        $comandos = new Comandos('commands_texts.txt');
        
        if($textoComando = $comandos->getTextoComando($this->mensaje)) {
            $url =  $this->website.'/sendMessage?chat_id='.
                    $this->chatId.'&parse_mode=HTML&text='.
                    urlencode($textoComando);
            file_get_contents($url);
        }
    }

    public function sendVoice() {
        if (file_exists('./sounds/' . substr($this->mensaje, 1) . '.ogg')) {
            $api_url = $this->website . '/sendVoice';
            $curl    = curl_init();
            curl_setopt($curl, CURLOPT_URL, $api_url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, [
                'chat_id'   => $this->chatId,
                'voice'     => new CURLFile('./sounds/' . substr($this->mensaje, 1) . '.ogg'),
            ]);
            curl_exec($curl);
            curl_close($curl);
        }
    }

    public function sendChatGPT() {
        if ($this->mensaje == '/gpt'){
            $env = file_get_contents('.env');
            $lines = explode(PHP_EOL, $env);
            foreach ($lines as $line) {
                putenv($line);
            }

            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . 'TOKEN_GPT',
            ];
            $data = [
                'model' => 'text-davinci-003',
                'prompt' => getenv('GPT').substr($this->mensaje, 4),
                'temperature' => 0.5,
                'max_tokens' => 200,
                'stop' => ['\n'],
            ];
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'https://api.openai.com/v1/completions');
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($curl);
            curl_close($curl);
            $message = json_decode($response, true)['choices'][0]['text'];

            $url =  $this->website.'/sendMessage?chat_id='.
                    $this->chatId.'&parse_mode=HTML&text='.
                    urlencode($message);
            file_get_contents($url);
        }

    }
}
?>