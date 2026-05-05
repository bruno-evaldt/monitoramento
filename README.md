# 💧 Sistema de Monitoramento de Consumo de Água

Bem-vindo ao repositório do Sistema de Monitoramento de Água via MQTT. Este projeto foi desenvolvido para medir, registrar e exibir o consumo de água por apartamento em tempo real, utilizando microcontroladores e a framework Laravel.

---

## 📋 1. Requisitos Necessários e Ferramentas Utilizadas

Certifique-se de que seu ambiente de desenvolvimento atenda às seguintes versões (ou superiores):

### Back-end & Front-end (Aplicação Web)
* **PHP:** `^8.3`
* **Composer:** `^2.x`
* **Node.js:** `^20.x` ou superior (com `npm` ou `yarn` para compilar assets via Vite)
* **Banco de Dados:** MySQL, PostgreSQL ou SQLite (configurado por padrão)
* **Laravel Framework:** `^12.0`
* **Filament Admin:** `^5.x` (Painel de administração)
* **Livewire:** `^3.x` (Componentes reativos)

### Internet das Coisas (IoT)
* **Broker MQTT:** Mosquitto, HiveMQ, EMQX, etc. (Opcionalmente, pode-se usar um broker público para testes rápidos como `broker.emqx.io`).
* **Cliente MQTT PHP:** `php-mqtt/client` `^2.3`
* **Hardware (Placa):** ESP32 ou ESP8266
* **Sensores:** Sensor de fluxo de água com saída de pulso.

---

## 🚀 2. Comandos para Rodar o Projeto

Siga este passo a passo para clonar e rodar o projeto localmente:

**1. Clone o repositório**
```bash
git clone <URL_DO_REPOSITORIO>
cd <NOME_DA_PASTA_DO_PROJETO>
```

**2. Instale as dependências do PHP e do Node**
```bash
composer install
npm install
```

**3. Configure o arquivo de ambiente (.env)**
Faça uma cópia do `.env.example` e renomeie para `.env`:
```bash
cp .env.example .env
```
Gere a chave da aplicação:
```bash
php artisan key:generate
```

**4. Configure o Banco de Dados, MQTT e E-mail no `.env`**
Abra o arquivo `.env` e configure conforme seu ambiente:
```dotenv
# Configuração do Banco (exemplo SQLite)
DB_CONNECTION=sqlite

# Configuração do MQTT
MQTT_HOST=broker.emqx.io
MQTT_PORT=1883
MQTT_CLIENT_ID=laravel_listener_local

# Configuração de E-mail (Para avisos de vazamento)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu_email@gmail.com
MAIL_PASSWORD=sua_senha_de_app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=seu_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**5. Rode as Migrations (Cria o banco de dados e tabelas)**
```bash
php artisan migrate --seed
```

**6. Inicialize os Serviços do Projeto**
Você precisará de **4 terminais rodando simultaneamente** para que tudo funcione perfeitamente:

* **Terminal 1: Servidor Web Laravel**
  ```bash
  php artisan serve
  ```
* **Terminal 2: Compilador de Assets (Vite)**
  ```bash
  npm run dev
  ```
* **Terminal 3: Processador de Filas (Para disparar os e-mails assincronamente)**
  ```bash
  php artisan queue:work
  ```
* **Terminal 4: Listener MQTT (Fica escutando a placa em tempo real)**
  ```bash
  php artisan mqtt:listen
  ```

---

## 🔌 3. Como Configurar com a Placa (ESP32/ESP8266)

Para que a placa converse com o nosso sistema, o código C++/Arduino dela precisa estar configurado para conectar no mesmo **Broker MQTT** definido no arquivo `.env` da aplicação Laravel.

### Estrutura de Tópicos MQTT

1. **Envio de Leituras (Telemetria):**
   A placa deve **publicar (publish)** as leituras de consumo de água no formato JSON neste tópico:
   `condominio/readings/{MAC_DA_PLACA}/{PINO_DO_SENSOR}`

   *Exemplo de Tópico:* `condominio/readings/A1:B2:C3:D4:E5:F6/36`
   
   *Exemplo de Payload JSON (Leitura automática):*
   ```json
   {
     "volume": 15.5
   }
   ```
   *Exemplo de Payload JSON (Resposta a uma leitura manual solicitada via painel):*
   ```json
   {
     "volume": 15.5,
     "type": "manual"
   }
   ```

2. **Recepção de Comandos (Leituras Manuais):**
   A placa deve se **inscrever (subscribe)** no tópico de comandos para saber quando o síndico clica em "Solicitar Leitura" no painel. O tópico sugerido é:
   `condominio/commands/{MAC_DA_PLACA}/{PINO_DO_SENSOR}`

### Registro do Dispositivo no Sistema
Dentro do painel Administrativo (Filament), você deve ir até a seção de **Dispositivos (Devices)** e cadastrar a placa informando exatamente o **Endereço MAC** e o **Pino do Sensor** que você configurou no hardware. O sistema usa esses dois dados para descobrir de qual apartamento a leitura veio.

---

## 🏗️ 4. Esquema de Como Está Funcionando (Arquitetura)

O ecossistema é assíncrono e baseado em eventos via mensageria (MQTT). Abaixo um fluxo simples:

```mermaid
graph TD
    A[💧 Sensor de Fluxo] -->|Pulsos Elétricos| B(Microcontrolador - ESP32)
    B -->|WiFi + MQTT Publish| C((Broker MQTT - emqx))
    
    C -->|MQTT Subscribe| D[Terminal Listener - artisan mqtt:listen]
    
    D -->|Valida MAC e Salva| E[(Banco de Dados)]
    D -->|Verifica Vazamento contínuo| F{Vazamento?}
    
    F -->|Sim (3/8 minutos)| G[Job na Fila - Laravel Queue]
    G -->|Dispara Alerta| H[📧 E-mail do Morador]
    
    I[Painel Web - Administrador/Morador] <-->|Consulta Dashboard| E
    I -->|Solicita Leitura Manual| C
```

### Resumo do Fluxo:
1. A **água passa** e gira o rotor do sensor físico.
2. A **Placa (ESP32)** calcula o volume baseado nos pulsos.
3. A placa empacota a leitura em um JSON e publica no **Broker MQTT**.
4. O comando `php artisan mqtt:listen` do Laravel está conectado ao Broker. Ao escutar a mensagem, ele valida se a placa (MAC/Pin) existe no banco de dados.
5. Sendo uma placa válida, ele **grava o histórico** vinculando ao apartamento correspondente.
6. A lógica analisa: se o fluxo de água se mantiver contínuo e ininterrupto por 3 ou 8 minutos, o sistema aciona a fila do Laravel para **enviar um e-mail de aviso de vazamento** ao morador (Job em background).
7. Moradores e Síndicos visualizam os gráficos e dados no **Painel Dashboard (Filament)**.

---

## 👥 5. Níveis de Acesso (Roles)

O sistema utiliza controle de permissões (ACL) no painel do Filament para garantir que os dados sejam acessados apenas pelas pessoas autorizadas.

* **Administrador / Síndico:**
  Tem acesso total ao painel. Pode visualizar os dados de consumo de **todos** os apartamentos, registrar novas placas (devices), gerenciar usuários/moradores e emitir relatórios ou solicitar leituras manuais das placas do condomínio.

* **Morador (Resident):**
  Acesso restrito. Ao fazer login no dashboard, o morador enxerga **apenas as métricas e o histórico do seu próprio apartamento**. Ele pode verificar o consumo diário, mensal e visualizar os gráficos do seu uso pessoal, bem como fechar o fluxo de água de sua residência, mas não tem acesso aos dados de vizinhos ou configurações do sistema.
