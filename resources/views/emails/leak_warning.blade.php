<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta de Consumo Contínuo</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f7fa; margin: 0; padding: 40px 0; color: #333333;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        <!-- Header -->
        <tr>
            <td style="background-color: {{ $isFinalWarning ? '#e53e3e' : '#f6ad55' }}; padding: 30px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">
                    @if($isFinalWarning)
                        ⚠️ ALERTA FINAL
                    @else
                        💧 Aviso de Consumo Contínuo
                    @endif
                </h1>
            </td>
        </tr>
        
        <!-- Body -->
        <tr>
            <td style="padding: 40px 30px;">
                <p style="font-size: 16px; line-height: 1.6; margin-bottom: 20px;">Olá <strong>{{ $apartment->user->name }}</strong>,</p>
                
                <p style="font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
                    Identificamos uso constante água no apartamento <strong>{{ $apartment->number }}</strong> localizado no Bloco <strong>{{ $apartment->block }}</strong>.
                </p>

                @if($isFinalWarning)
                <div style="background-color: #fff5f5; border-left: 4px solid #e53e3e; padding: 15px; margin-bottom: 20px;">
                    <p style="color: #c53030; font-weight: bold; margin: 0;">Este é o nosso alerta final. O consumo de água permaneceu contínuo.</p>
                </div>
                @endif

                <p style="font-size: 16px; line-height: 1.6; margin-bottom: 30px;">
                    Recomendamos fortemente que verifique as instalações do seu apartamento para evitar desperdício e sustos na fatura.
                </p>
            </td>
        </tr>
        
        <!-- Footer -->
        <tr>
            <td style="background-color: #f8fafc; padding: 20px; text-align: center; border-top: 1px solid #edf2f7;">
                <p style="font-size: 14px; color: #718096; margin: 0;">
                    Atenciosamente,<br>
                    <strong>Sistema de Monitoramento</strong><br>
                    Condomínio Inteligente
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
