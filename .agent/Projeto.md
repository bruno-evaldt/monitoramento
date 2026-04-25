# Plano de Ação: Sistema de Monitoramento de Água (TCC)

Este documento detalha a arquitetura, os passos de implementação e as tecnologias sugeridas para atender a todos os requisitos do seu projeto de TCC de individualização do consumo de água com a placa Kincony.

## Avaliação: PWA vs Aplicativo Nativo (Mobile)

Para o escopo de um TCC e desenvolvimento de um protótipo, usaremos **PWA (Progressive Web App)** em vez de um aplicativo nativo.
O PWA permitirá que a aplicação web seja instalada nos celulares dos usuários e admins, gerando atalhos na tela inicial, responsividade móvel nativa e capacidade de receber notificações (Web Push).

---

## Arquitetura e Comunicação com a Placa Kincony (MQTT & API REST)

Como o sistema será hospedado em nuvem para demonstração, forneceremos duas vias de comunicação para a placa Kincony:

### 1. Comunicação via MQTT (Principal)
1. **Medição Periódica:** A placa Kincony publica os dados de volume no tópico MQTT (ex: `condominio/apartamento_id/fluxo`). O backend Laravel estará escutando esses tópicos.
2. **Controle de Válvula:** O Laravel publicará uma mensagem no tópico (ex: `condominio/apartamento_id/valvula/set` com payload `FECHAR`). A placa Kincony receberá a mensagem e fechará a solenóide.

### 2. Comunicação via API REST (Backup/Alternativa)
Para garantir flexibilidade:
- **`POST /api/devices/{mac_address}/readings`**: Endpoint onde a placa pode enviar via JSON o volume lido.
- **`GET /api/devices/{mac_address}/commands`**: A placa pode bater nesse endpoint a cada X minutos para verificar comandos da válvula.

---

## 1. Banco de Dados, Modelos e Perfis

As migrations atuais (`users`, `apartments`, `devices`, `readings`, `valve_logs`) estão ótimas. Adicionaremos a coluna `daily_limit_volume` na tabela de apartamentos para a regra de limite.
**Perfis de Acesso (Roles & Permissions):**
Vamos utilizar o plugin **Filament Shield** (`bezhansalleh/filament-shield`), que gera a interface visual para criar os perfis de `Admin` e `Resident`.

---

## 2. Nova Regra de Negócio: Vazamento e Consumo Excessivo

**Situação 1: Suposto Vazamento (Fluxo Contínuo)**
- Se a placa reportar um fluxo de água, mesmo que mínimo, repetidas vezes sem nenhuma interrupção por X minutos consecutivos, o sistema disparará um alerta.

**Situação 2: Consumo Excessivo (Estouro de Meta)**
- Se a soma das leituras do apartamento em um único dia ultrapassar o `daily_limit_volume` (Limite Diário), o sistema dispara um alerta de Consumo Excessivo.

### Métodos de Notificação (Aprovados)
1. **E-mails:** Para segurança da demonstração do TCC, alertas também chegarão na caixa de entrada.
2. **Sistema e Web Push:** As notificações ficarão salvas no banco de dados e aparecerão no "sino de alertas" dentro do painel do morador/admin, e saltarão na tela do celular como notificações PWA.

---

## 3. Desenvolvimento das Interfaces (Frontend com Filament / Livewire)

Vamos utilizar o **Filament v3**, que já traz o Livewire v3 e o Tailwind CSS.

**Estratégia de Painéis (Filament Panels):**
1. **Painel Admin (`/admin`):**
   - Gráficos gerais do consumo do condomínio e Lista geral de Alertas/Vazamentos.
   - Gerenciamento de Moradores e Apartamentos usando **Filament Shield**.
   - Botões para abrir/fechar válvulas.
2. **Painel Morador (`/app`):**
   - Dashboard focada no seu próprio apartamento (`auth()->user()->apartment`).
   - Cards com o consumo do mês e do dia.
   - Botão para **Trancar Válvula de Água** e **Solicitar Leitura Atual**.
   - Visão dos alertas de vazamento.

---

## 4. Cronograma de Execução 

O passo a passo que será executado:

1. **Tarefas de Infra e API:** Criar a tabela de Notificações, migrar coluna de limites, e construir os Endpoints da API REST de backup.
2. **Integração Filament & Shield:** Instalar o Filament v3 e o `filament-shield`. Criar as Roles `Admin` e `Resident`.
3. **Recursos Filament:** Criar as páginas (Resources/Widgets) para visualizar Apartamentos, Leituras, Logs de Válvula.
4. **Lógica MQTT:** Implementar um Comando Laravel (ex: `php artisan mqtt:listen`) para escutar os tópicos em tempo real.
5. **Regras e Alertas:** Criar a lógica de Vazamento e Consumo Excessivo. Implementar o envio de E-mails e a notificação in-app (Sino de alertas do Filament).
6. **Transformar em PWA:** Aplicar o manifest e configurações para a instalação móvel.
