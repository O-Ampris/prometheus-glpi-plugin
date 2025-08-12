# Chamado Externo Plugin GLPI

O plugin em questão fornece a seguinte funcionalidade de adicionar na aba de criação/edição de chamados (Tickets), adicionando a aba de 
responsável externo, prazo de atendimento externo e status externo. Nisso o operador do sistema pode adicionar essas informações extras ao chamado e editar elas no sistema como preferir, sendo esses dados persistentes no banco de dados do GLPI usando sua própria table personalizada para tal.

# Instalação (GLPI)

Para instalar o plugin há duas formas, antes de qualquer coisa, recomenda-se baixar ou clonar o repositório para deixar tudo preparado, quando você for baixar, haverá a pasta data, que possui os dados do `GLPI` e do banco de dados (`mysql`), assim não necessitando o setup inicial do `GLPI` assim que ele é baixado.

## Local (Docker)

Para esta forma de instalação do plugin, certifique-se de ter o `docker` instalado e também o `docker-compose` para rodar os serviços do banco de dados e do glpi ao mesmo tempo, após se certificar, de ter os pacotes instalados, se certifique de iniciar o daemon do docker, para conseguir utilizar suas funcionalidades, caso tenha baixado o docker desktop, apenas abrí-lo irá iniciar automaticamente o serviço, caso contrário refira-se ao método de iniciar serviços de seu sistema operacional.

Dentro da pasta, siga as seguintes instruções:

```bash
docker compose up -d
```

Este comando irá inicializar os serviços descritos no arquivo `docker-compose.yml` do projeto.

> [!WARNING]
> Caso você se depare com o erro de não conseguir inicializar o driver de rede existente nos serviços, verifique sua instalação, porém as vezes que me ocorreu, apenas reiniciar o dispositivo foi suficiente para que o problema vá embora.

Após rodar este comando, seu projeto estará iniciando, e pode levar alguns segundos até o `docker` inicializar os processos do `mysql` e do servidor `apache` do GLPI, então recomendo aguardar um pouco, após rodar o comando e ele retornar que tudo foi iniciado, após o início, acesse o projeto em `http://localhost/` ou `http://127.0.0.1/` se preferir, o projeto é servido na porta `:80`.

## Local (XAMPP/Manual)

Para realizar a instalação manual, supondo que você esteja rodando `XAMPP` ou tenha o servidor `apache` e o serviço `mysql`/`mysqld` rodando, mova a pasta presente em `data/glpi-db` para o local onde vive as informações do seu `mysql` por exemplo `/var/lib/mysql` e despeje os arquivos dessa pasta dentro da pasta mysql, não copie a pasta junto.

> [!CAUTION]
> Essa ação irá excluir seus dados presentes no `mysql` e pode acarretar em incompatibilidade, faça a seu próprio risco.

Caso não queira realizar essa medida drástica, inicie o `mysql` e siga as instruções em `Local (Novo/Manual)`.

A seguir, vá até a pasta que seu servidor está servido `www` ou `htdocs` por exemplo, certifique-se de estar rodando o `PHP` neste servidor, sendo o `PHP` compatível com o `GLPI`, caso contrário um erro aparecerá pedindo para realizar um `upgrade` ou `downgrade` em seu `PHP`, e dentro da pasta servida pelo seu servidor, coloque todo o conteúdo da pasta `data/glpi-web`, apenas o conteúdo para tudo ser servido na raíz do servidor, caso contrário terá de acessar o caminho até `http://localhost/glpi-web`, que pode causar efeitos indesejáveis.

## Local (Novo/Manual)

Para realizar a instalação manual, primeiro deve-se confirmar de ter o `mysql` e algum servidor `apache` ou `nginx` rodando que já tenha sido configurado para suportar `PHP`, e se certificar de ter a versão correta do `PHP` para rodar o projeto localmente. Após ter tudo configurado vá até a [Página de Download GLPI](https://glpi-project.org/downloads/), e obtenha a versão presente no website oficial, após baixar, coloque todo contéudo na raíz de seu servidor web de preferência.

Acesse o endereço local de sua máquina (`localhost`) e então siga até o fim do setup inicial do `GLPI` isso te dará uma instalação completamente nova do `GLPI` em seu ambiente local, deixando por sua conta e risco qualquer modificação ou alteração presente para fazê-lo funcionar corretamente.

## Login

Para logar no `GLPI` independentemente do método de instalação, use as credenciais `glpi`, tanto para `login` quanto `senha`, pois em um `GLPI` não configurado, essas credênciais dão permissões de um super-admin, te permitindo realizar todos os testes e alterações no sistema sem se preocupar com permissionamento ou outras inconvniências que possa acabar ocorrendo utilizando outros perfis existentes, caso seja um ambiente em produção, sinta-se livre para usar seu perfil de administrador.

# Instalação (Plugin)

Para ativar o plugin quando já dentro do `GLPI`, certifique-se de que a pasta `chamadoexterno` esteja dentro do `GLPI` na pasta `plugins`, ficando algo como `plugins/chamadoexterno`, caso você queira verificar se tudo correu corretamente, acesse a aba `Configurações` > `Plugins` no próprio `GLPI` e na lista deve aparecer o plugin `Chamado Externo` (`chamadoexterno`) desenvolvido por `Fabio Araújo`, após isso certifique-se de que esteja instalado (ícone de pasta na lateral está com um `-` e não um `+`), e que o plugin esteja ativo (toggle está verde e marcado).

E pronto, seu plugin está ativo! sinta-se a vontade para utilizar as novas funcionalidades que ele te proporciona no sistema!

# AVISOS!

Caso você enfrente problemas de permissão ao rodar o `docker compose`, adicione ao `service` do `mysql` o campo `user: "1000:1000"` e o problema não irá mais ocorrer, caso mesmo assim esteja com problema, apague o arquivo `data/glpi-db/mysql.sock`, pois como ele é um link simbólico, em alguns casos o link pode não funcionar mais e precisa ser gerado novamente pelo próprio container docker.

# Finalizações

Agora a esta altura seu plugin `GLPI` para chamados externos já está completamente funcional, faça um bom uso, conferindo os novos campos adicionados na aba de criação/edição de chamados, fique a vontade para realizar , e não deixe de deixar seu feedback se possível!