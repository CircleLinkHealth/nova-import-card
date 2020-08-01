<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\TrixField;
use Illuminate\Database\Seeder;

class CareAmbassadorDefaultScriptsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TrixField::insert([
            [
                'type'     => TrixField::CARE_AMBASSADOR_SCRIPT,
                'language' => TrixField::ENGLISH_LANGUAGE,
                'body'     => '<p><strong>Enroller:</strong> Hi, {patient}? (let&rsquo;s be assumptive)&nbsp;</p>

<p><strong>Patient:</strong> Yes? Who&rsquo;s this?&nbsp;</p>

<p><strong>Enroller:</strong> Hi {patient}, this is {enroller}&nbsp;calling on behalf of {doctor} at {practice}. How are you doing today?&nbsp;</p>

<p><strong>Patient:</strong> (patient will respond, be positive and compassionate)&nbsp;</p>

<p><strong>Enroller:</strong> Great! So the reason I&rsquo;m calling is {doctor} is moving forward with a new benefit to patients. You should have received information about it in the mail. {doctor}&nbsp;now has a team of registered nursing coaches to provide monthly check-in calls with you in-between your appointments. They understand that it can sometimes be difficult to get ahold of the practice to get your prescriptions refilled, your appointments scheduled or to have your general questions answered. Are you experiencing anything like that?&nbsp;</p>

<p><strong>Patient:</strong> Yes/No&nbsp;</p>

<ul>
	<li><u>If patient is experiencing these challenges:</u> Ah, yes. {doctor}&nbsp;does recognize this.&nbsp; Some of the other things that patients like about this program are...&nbsp;

	<ul>
		<li>That the majority of the time, it&rsquo;s completely free as this service is completely covered by Medicare and your supplemental insurance.&nbsp;</li>
		<li>That they have a dedicated nurse coach to answer any questions you may have while relaying any information back to your doctor.&nbsp;</li>
		<li>That they have access to local resources within the community such as transportation assistance to appointments.&nbsp;</li>
		<li>And your care team can loop in other services, like behavioral health specialists if needed.</li>
	</ul>
	</li>
	<li><u>If patient is NOT experiencing these challenges:</u> That&rsquo;s good to hear! This program is optional, so totally up to you. But there are other benefits I&rsquo;d like you to be aware of. {doctor}&nbsp;has also invested in this program because research has also shown that similar programs keep patients happier, healthier and out of the hospital. Since {doctor} will have better transparency into how you are doing, this in-turn ensures they provide the best medical advice and treatment. Your care team can also loop in other services, like behavioral health specialists if needed.</li>
</ul>

<p><strong>Enroller:</strong> When you go to your general doctor for a normal visit, do you typically have a copay after hitting your deductible, or is it covered by your insurance?</p>

<ul>
	<li><u>If copay:</u> Not a problem. Currently, we are seeing a small copay for this program that ranges between $7-$21 per month, with the average being about $8. All of the patients currently enrolled in the program feel that this cost is reasonable given the benefits from our care coaches. <em>(Proceed to Questions section)</em></li>
	<li><u>If covered:</u> Great, this means that the program is likely completely covered by your insurance. Do you have Traditional Medicare or a Medicare Advantage plan?
	<ul>
		<li>Traditional Medicare: Perfect, this program should be completely covered then by your insurance. <em>(Proceed to Questions section)</em></li>
		<li>Medicare Advantage: Ok got it. Currently, we are seeing a small copay for this program that ranges between $7-$21 per month, with the average being about $8. All of the patients currently enrolled in the program feel that this cost is reasonable given the benefits from our care coaches. <em>(Proceed to Questions section)</em></li>
		<li>Unsure: That&rsquo;s okay! In most cases, no copay will mean no fee for this service. If you happen to have a Medicare advantage plan, there may be a small copay for this program that ranges between $7-$21 per month, with the average being about $8. All of the patients currently enrolled in the program feel that this cost is reasonable given the benefits from our care coaches. <em>(Proceed to Questions section)</em></li>
	</ul>
	</li>
</ul>

<p><u><strong>Questions</strong></u></p>

<p><strong>Enroller:</strong> Do you have any questions at this time?</p>

<ul>
	<li><u>If no questions:</u> Perfect. Next I can quickly take down your contact preferences for your monthly calls - how does that sound?

	<ul>
		<li>If yes: <em>(Proceed to Enrollment section)</em></li>
		<li>If no: That&rsquo;s perfectly fine, we do understand that a personalized care program is not for everyone and we do appreciate your time today. May I ask if there is any specific reason you&rsquo;re not interested in giving the program a try for a few months?&nbsp;</li>
	</ul>
	</li>
	<li><u>If not sure:</u> Is there anything I can explain further to help you make up your mind? The program is voluntary and you can withdraw at any time. Would you like to give it a try for a month or two?</li>
	<li><u>If a patient has questions</u>: Answer questions using the <a href="https://circlelinkhealth.zendesk.com/hc/en-us/articles/360037756052-Enrollment-FAQ-s-and-Common-Objections">FAQ page</a> as a reference.
	<ul>
		<li>Any other questions before we get you enrolled?
		<ul>
			<li>If no: <em>(Proceed to Enrollment section)</em></li>
			<li>If not interested: That&rsquo;s perfectly fine, we do understand that a personalized care program is not for everyone and we do appreciate your time today. May I ask if there is any specific reason you&rsquo;re not interested in giving the program a try for a few months?&nbsp;</li>
			<li>If not sure: Is there anything I can explain further to help you make up your mind? The program is voluntary and you can withdraw at any time. Would you like to give it a try for a month or two?</li>
		</ul>
		</li>
	</ul>
	</li>
</ul>

<p><u><strong>Enrollment</strong></u></p>

<p><strong>Enroller:</strong> I will say if you&rsquo;re currently on dialysis or in hospice services, that would unfortunately disqualify you from this program. Do either of these apply to you?</p>

<ul>
	<li><u>If no:</u> <em>(Proceed to next question)</em></li>
	<li><u>If yes:</u> Thank you. Medicare currently only reimburses for one program at a time, so we unfortunately can&#39;t enroll you in this personalized care program at this time. If anything changes, please give us a call at this same number I called you from. Have a nice rest of your day!</li>
</ul>

<p><strong>Enroller:</strong> Alright, the next step is the nurse will call you to give you a welcome call to set you up on the program. After that, they&rsquo;ll check-in with you once a month as I outlined earlier. <em>(if you receive pushback on the number of times a nurse will call per month, let them know we can reduce it to every other month) </em></p>

<p><strong>[Click the green CONSENTED button and follow the steps and questions there]</strong></p>
',
            ],
            [
                'type'     => TrixField::CARE_AMBASSADOR_SCRIPT,
                'language' => TrixField::SPANISH_LANGUAGE,
                'body'     => '<div><strong>Reclutador:</strong> Hola, {patient}? / (vamos a presumir que contestó elpaciente) /<br><br><strong>Paciente:</strong> Si? ¿Quién es?<br><br><strong>Reclutador:</strong> Hola {patient}, soy {enroller} llamando en nombre de {doctor} en {practice}. ¿Cómo estás hoy?<br><br><strong>Paciente: </strong>(El paciente responderá... Ten una actitud positiva y compasiva).<br><br><strong>Reclutador:</strong> Primero, ¿te agarré en un mal momento?<br><br><strong>Paciente:</strong> Sí / No<br><br><strong>Si no es un buen momento para hablar</strong><br><strong>Reclutador: </strong>No se preocupe en absoluto, ¿cuándo sería un mejor momento para volverla a llamar? Solo necesito un minuto de su tiempo para explicarle un nuevo programa que el doctor {doctor} está brindando a sus pacientes.<br><br><strong>Si es un buen momento para hablar</strong><br><strong>Reclutador:</strong> ¡Genial! La razón por la cual estoy llamando es porque {doctor} ha desarrollado un nuevo programa desde su última cita en {last visit}. Este programa está dirigido a todos sus pacientes y él / ella pensó que usted podría estar interesado en aprovecharlo.<br><br><strong>Reclutador: </strong>Entonces, el programa funciona de la siguiente manera: el doctor {doctor} ahora cuenta con un equipo de enfermeras registradas, entrenadas para realizar llamadas de control mensuales entre sus citas. El Doctor {doctor} no siempre tiene tiempo suficiente para explicar cada cosa durante las breves visitas con sus pacientes . También se da cuenta de que puede ser difícil ponerse en contacto con la práctica, para volver a surtir las recetas o programar las citas o para hacer preguntas. ¿Estás experimentando algo así?<br><br><strong>Paciente:</strong> Sí / No<br><br><strong>Si el paciente está experimentando estos desafíos:</strong><br><strong>Reclutador:</strong> Ah, sí. {doctor} lo reconoce y le quiero mencionar algunos aspectos que otros pacientes encuentran muy valioso sobre este servicio.<br><br></div><ol><li>Que la mayoría de las veces, es completamente gratuito ya que este servicio está completamente cubierto por Medicare y cualquier seguro complementario.</li><li>Que tienen una enfermera entrenada dedicada a responder a cualquier pregunta que tengan, transmitiendo cualquier información al médico.</li><li>Que tengan acceso a recursos locales dentro de su comunidad, como recetas para satisfacer sus necesidades dietéticas.</li></ol><div><br><strong>Paciente:</strong> responderá acorde a cuanto dicho.<br><br><strong>Reclutador:</strong> Parece que nuestro minuto se está acabando. Pero basándome en [problema que reconocieron anteriormente] - me encantaría brindarte un poco más de información sobre este programa y responder a cualquier pregunta que pueda tener; ya que parece que este programa sería muy adecuado para usted. <br><br>Si usted tiene 5 minutos más para discutir, excelente, si no, ¿hay un mejor momento, mañana u otro día, en que pueda llamarla para seguir hablando un poco mas sobre este programa?<br><br><strong><em>[Si el paciente quiere verificar, anímelo a llamar a la práctica, luego programe una llamada más larga en uno o dos días]</em></strong><br><br><strong>Si el paciente quiere continuar la conversación:</strong><br><strong>Reclutador:</strong> ¡Maravilloso! Como mencioné anteriormente, una enfermera registrada estaría llamándola por teléfono, normalmente, dos veces al mes. Ellas están aquí para reponer las recetas, programar citas y responder a cualquier pregunta que pueda tener.<br><br>Así es como funciona:<br><br></div><ol><li>Están aquí para transmitir cualquier información entre usted y su médico. Tenga en cuenta que todavía puede comunicarse con [Nombre del médico] como lo hace usualmente; lo que sea mas conveniente para usted.</li><li>La enfermera también puede ayudarlo con la programación de citas, el surtido de recetas, los resultados de las pruebas y cualquier pregunta general.</li><li>La mayoría de las veces, es completamente gratuito ya que este servicio está completamente cubierto por Medicare y su seguro complementario.</li></ol><div><br><strong>Reclutador:</strong><span style="color:green"> Su enfermera y {doctor} también pueden incluir otros servicios, como especialistas de salud del comportamiento, si es necesario! (decir ésto con entusiasmo).</span><br><br><strong>Reclutador:</strong> ¿Tiene alguna pregunta en este momento?<br><br><strong>Paciente:</strong> (podría tener preguntas; prepárate para consultar la guía o las preguntas<br>frecuentes abajo)<br><br><strong>Reclutador:</strong> ¿Y cómo suena todo esto hasta ahora?<br><br><strong>Paciente: </strong>(expresarán su opinión con comentarios positivos / no estoy seguro).<br><br><span style="color:red"><strong><em>(si el paciente no está seguro, pregunte por sus dudas y vea si puede explicarles mejor el programa)</em></strong></span><br><br><strong>Reclutador: </strong>Genial - Hay algunos detalles adicionales que quisiera explicar rápidamente.<br><br><strong>Reclutador:</strong> El programa es opcional y se ofrece a través de Medicare. La mayoría de los pacientes no tienen que pagar nada, ya que la mayoría de los planes de seguros complementarios terminan cubriendo el copago completo. De vez en cuando, no está cubierto y puede haber un pequeño copago entre $ 7- $ 21 / mes, pero estamos viendo que el promedio es de alrededor $8.<br><br>Como recordatorio, puede cancelar la suscripción en cualquier momento llamando a su enfermera para que se retire.<br><br><strong>Paciente:</strong><span style="color:red"> (reconocerá el costo. Si el paciente presenta inquietudes sobre el costo, consulte la sección 1a en Preguntas frecuentes)</span><br><br><strong>Reclutador:</strong> <span style="color:green">Quiero añadir que si actualmente usted está en diálisis o recibe<br>servicios de hospicio, esto lo descalificará de nuestros servicios.</span><br><br><strong>Paciente:</strong> (El paciente reconocerá o no si se encuentra en una de esas situaciones).<br><br><strong>Reclutador:</strong> Ahora, todo lo que necesitamos es su luz verde (<span style="color:orange">su aprobación</span>) y podemos proceder a registrarla en el programa . ¿Eso suena bien (mostrar entusiasmo)?<br><br><strong>Paciente:</strong> Sí / No / No estoy seguro<br><br><strong>Si la respuesta es NO:</strong><br><strong>Reclutador:</strong> No hay absolutamente problema, entendemos que un programa de atención personalizada no es para todos y apreciamos su tiempo hoy. ¿Puedo preguntar si hay alguna razón específica por la cual no está interesado en probar el programa durante algunos meses?<br><br><strong>SI NO ESTÁ SEGURO:</strong><br><strong>Reclutador:</strong> ¿Tiene alguna pregunta? Anteriormente, usted mencionó que estaba experimentando [el problema que reconocieron anteriormente]. ¿Le sería de utilidad si le explico en términos generales cómo la enfermera asignada podría ayudar?<br><br>(Esté preparado para explicar el proceso del problema que están experimentando)<br><br><strong>Si la respuesta es SI:</strong><br><strong>Reclutador: </strong>Muy bien, el siguiente paso será que la enfermera lo va a llamar para darle la bienvenida y aviar el programa. Después de eso, la enfermera se contactará con usted dos veces al mes, tal como lo mencioné anteriormente.<br><br><span style="color:red"><strong><em>(Si el paciente muestra un rechazo en el número de veces que la enfermera llama por mes, hágales saber que podemos reducir el numero de llamadas a una vez por mes)</em></strong></span><br><br><strong>Reclutador:</strong> ¿Y es este el mejor número para poder contactarlo?<br><br><strong>Paciente: </strong>Si. (o proporcionarán un número alternativo)<br><br><strong>Reclutador:</strong> ¡Gracias! ¿Le importaría proporcionarnos su dirección de corre electrónico y su domicilio?<br><br><strong>Paciente:</strong> Sí (puede que no proporcione un correo electrónico, pero debería proporcionar la dirección).<br><br><strong>Reclutador: </strong>Si está viendo algún especialista, ¿le importaría compartir su nombre e información? La razón por la cual estoy preguntando es porque su enfermera podría coordinar potencialmente a todo su equipo de atención medica, para ahorrarle tiempo en la transmisión de la información necesaria.<br><br><strong>Reclutador: </strong>Eso es todo lo que necesito, la enfermera registrada le llamará desde este mismo número, dentro de la próxima semana, para presentarse y brindarle su información de contacto, en caso de que necesite comunicarse con ellos. Como recordatorio, puede retirarse en cualquier momento. ¡Que tengas un buen día -Gracias de nuevo por tu tiempo!<br><br><span style="color:green"><strong><em>{ESCUCHE y escriba las notas lo más detalladas posible. Haga su mejor esfuerzo para responder a cualquiera de sus inquietudes, pero lo más importante es que tome notas específicas para que podamos comenzar a buscar las mejores respuestas}<br></em></strong></span><br></div><h3>PREGUNTAS FRECUENTES</h3><div><br><strong>1a. Si el paciente está preocupado por el costo</strong><br><strong>Reclutador:</strong> Sé que el costo puede ser una preocupación, y queremos reiterar que este servicio no es para todos. Pero como mencioné anteriormente, la mayoría de los pacientes no pagan en absoluto, ya que el seguro complementario generalmente lo cubre. Si descubre que termina con un copago, puede comunicarse con su enfermera especializada en cualquier momento para cancelar."<br><br><strong>1b. Si la respuesta es negativa, “no es para mí”</strong><br><strong>Reclutador: </strong>"No hay absolutamente problema, entendemos que el programa de atención personalizada no es para todos y apreciamos su tiempo hoy. Ya que el programa ha demostrado reducir las hospitalizaciones y su médico y su seguro creen que el programa puede ayudarlo,¿existe alguna razón específica por la cual no está interesado en probarlo durante algunos meses?"<br><br><strong>1c. Si la llamada te transfiere a un buzon de voz (un contestador automático) deja el siguiente mensaje de voz:</strong><br>"Hola, este es {enroller} llamando en nombre de {doctor} en {practice}. El doctor {doctor} lo ha invitado a tomar parte de su nuevo programa de atención personalizada.<br> <br>Por favor llámenos al número (XXX) XXX-XXXX para obtener más información.Tenga en cuenta que no hay nada de qué preocuparse, este programa solo permite al doctor tener un mejor cuidado de usted entre las visitas. Nuevamente el número es [(XXX) XXX-XXXX].<br><br><strong>1d. "Simplemente llamo a mi médico si necesito algo, no necesito una enfermera que llame"</strong><br><strong>Reclutador: </strong>{doctor} siempre tiene una política de puertas abiertas, pero debido a la cantidad de pacientes que tiene, quería invertir en este programa para garantizar que todos y cada uno de sus pacientes recibiesen la mejor atención entre visitas.<br><br><strong>1e. “No tengo seguro complementario y no puedo permitirme el copago”.</strong><br><strong>Reclutador:</strong> Entendemos que este programa personalizado no es adecuado para todos, debido a diversos factores. Queremos agradecerle por su tiempo de hoy. ¡Espero que tenga un buen día!<br><br><strong>1f. ¿Estás en la oficina de mi médico ahora?</strong><br><strong>Reclutador: </strong>No estamos ubicados en el consultorio de su médico. Nuestro equipo está formado por enfermeras registradas que operan en todo el país. Esto, a su vez, nos permite trabajar en horarios más flexibles para adaptarnos a su horario.<br><br><strong>1g. ¿Quién está ofreciendo este programa? ¿Para quién trabajas? ¿Es realmente mi médico?</strong><br><strong>Reclutador:</strong> {doctor} se ha asociado con nosotros para convertirnos en su solución para la gestión de atención crónica. Entendemos que puede tener inquietudes, por lo que le recomendamos que se comunique con él o le pregunte acerca de nuestros servicios durante su próxima visita a su oficina. ¿Necesita el número de teléfono de {doctor}?<br><br><strong>1h. “¿Por qué mi doctor me está ofreciendo esto? ¿Siente que algo está mal?"</strong><br><strong>Reclutador: </strong>Su médico entiende que preguntas sobre los medicamentos, las citas y la salud en general pueden surgir entre sus controles. {doctor} también entiende que cuando surgen esas preguntas, puede ser difícil comunicarse con él. Este programa ayuda a ampliar aún más esa línea de comunicación entre usted y {doctor}<br><br><strong>1i. “Estoy saludable, no necesito esto”.</strong><br><strong>Reclutador:</strong> Entendemos que este programa personalizado no es adecuado para todos, debido a diversos factores. Queremos agradecerle por su tiempo de hoy. ¡Espero que tenga un buen día!<br><br><strong>1j. “No quiero agregar cargos adicionales para los CMS (Centros de Servicios<br>de Medicare y Medicaid)"</strong><br><strong>Reclutador:</strong> Es exactamente por eso que {doctor} ha invertido en este programa: los estudios han demostrado que programas como estos realmente ahorran dólares a los contribuyentes. Estos programas están diseñados para mantenerlo más saludable, lo que a su vez ayuda a prevenir visitas inesperadas y costosas a la sala de emergencias.<br><br>Si está interesado, estaremos encantados de enviarle por correo electrónico información adicional que describe cómo Medicare ha ahorrado decenas de millones de dólares a través de programas como estos."<br><br><a href="https://www.modernhealthcare.com/article/20180220/NEWS/180229988/chronic- care-management-program-showing-signs-of-saving-money-improving-care">https://www.modernhealthcare.com/article/20180220/NEWS/180229988/chronic-<br>care-management-program-showing-signs-of-saving-money-improving-care</a><br><br><br></div><h3><span style="color:red">OBJECIONES COMUNES / CONSEJOS / PREGUNTAS FRECUENTES</span></h3><div><br>1. Se aplican coseguros, copagos y deducibles estándar, por lo que puede ser facturado por estos servicios hasta una vez por mes.<br><br>2. Los servicios de CCM están cubiertos por la Parte B de Medicare.<br><br>3. El seguro complementario puede cubrir el copago; consulta con tu compañía de seguros para detalles.<br><br>4. ¿Puede la enfermera llamarme cada 2+ meses en lugar de cada mes?<br><br><span style="color:cornflowerblue"><em>-"Definitivamente usted puede discutir la frecuencia de las llamadas con la enfermera, ya que él o ella sabrá lo que tenga más sentido para usted, basándose en las condiciones específicas que usted presenta. ¿Estaría bien si la enfermera le hace una llamada dentro de la próxima semana, para hablar sobre eso?"</em></span><br><br>5. ¿Hay un costo para esto? (Depende si tienen un seguro suplementario; la mayoría de los ancianos tienen algún tipo de seguro; si no tienen el segundo seguro tendrían que pagarlo a través de copago).<br><br><span style="color:cornflowerblue"><em>-"Probablemente no, pero sería mejor llamar a la persona encargada de la facturación para verificar."</em></span><br><br>6. ¿Es usted un proveedor verificado de gestión de atención personalizada?<br><br><em>-"Sí, trabajamos con (informe el nombre de los médicos), puede llamar al consultorio y puedo devolverle la llamada mañana después de que haya tenido la oportunidad de hablar con el Dr. _________. <span style="color:red">(También puedes dar el número del médico)</span>"<br></em><br>7. ¿Hay algún contrato?<br><span style="color:cornflowerblue"><em>-"No hay ninguno, es un compromiso que renovamos de mes a mes . Si lo desea, puede probar el servicio por un mes o dos y ver cómo le va."</span><br></em><br>8. ¿El programa incluirá visitas al hogar?<br><span style="color:cornflowerblue"><em>-"El programa NO incluirá visitas domiciliarias."</span><br></em><br>9. Si el paciente está en diálisis temporal, ¿sería elegible?<br><br><span style="color:cornflowerblue"><em>"Medicare solo cubre un programa, por lo tanto, si el paciente esta haciendo uso de servicios de hospicio o está en diálisis, Medicare simplemente no paga CCM."<br><br>Si el paciente está en diálisis temporal, puede ser elegible una vez que haya terminado, por lo que puede ser una buena idea averiguar cuándo se completará la diálisis para que podamos inscribirlo. NO debemos pedirles a los pacientes que paguen de su bolsillo si no son elegibles.</em></span><br><br><br><strong>Consejos:&nbsp;&nbsp;</strong><br>- Busca en Google el clima en el área que estás llamando, para que puedas hablar sobre eso y establecer una conexión con el cliente.<br>- Si el paciente se registra, déjale saber que "La enfermera lo llamará en aproximadamente una semana. ¿Hay algo que deberíamos avisarle a la enfermera antes de que lo llame? "<br>- Cuando llames a los pacientes, asegúrate de poner una sonrisa en tu cara :) Ella se puede sentir a través del teléfono!<br>- Asegúrese de hablar lentamente - algunas personas mayores pueden tener problemas de audición.<br>- Si hay un número alternativo, asegúrese de llamar a ese también si no logras comunicarte con el número primario.<br>- ¡Intenta establecer una conexión con el paciente! Cuanto más logres conectarte con el paciente, más probable será que se inscriba.</div>',
            ],
            [
                'type'     => TrixField::CARE_AMBASSADOR_UNREACHABLE_USER_SCRIPT,
                'language' => TrixField::ENGLISH_LANGUAGE,
                'body'     => '<p><strong>Enroller:</strong> Hi {patient}, this is {enroller} calling on behalf of {doctor} at {practice}. How are you doing today?&nbsp;</p>

<p>&nbsp;</p>

<p><strong>Patient:</strong> (patient will respond, be positive and compassionate)&nbsp;</p>

<p>&nbsp;</p>

<p><strong>Enroller:</strong> I&rsquo;m calling about {doctor}&rsquo;s personalized care program. We have you on the call list, but your nurse said that they have been missing you the last few times they tried to call. It may have just been bad timing on their part, but we wanted to check with you and see when is the best time to reach you. Do you prefer morning, afternoon or evening calls?</p>

<p>&nbsp;</p>

<p><strong>Patient: </strong>Positive or negative response</p>

<p>&nbsp;</p>

<p style="margin-left:40px"><strong>If positive response with preference:</strong> Great. Is there a day of the week or days of the week that work best for you?</p>

<p style="margin-left:40px"><em>Log preferences in the enrollment spreadsheet</em></p>

<p>&nbsp;</p>

<p style="margin-left:40px"><strong>If positive response, but preference is broad (&ldquo;Any time works&rdquo;):</strong> We usually find that it is easiest to pick specific times for calls so you know when to expect the check-in call. If you had to pick days and times during the week that work best, which would you choose?</p>

<p style="margin-left:40px"><em>Log preferences in the enrollment spreadsheet</em></p>

<p>&nbsp;</p>

<p style="margin-left:80px"><strong>End Call:</strong> Perfect. We will pass these preferences along to your nurse so that they can call you at your preferred times. Have a great rest of your day!</p>

<p>&nbsp;</p>

<p style="margin-left:40px"><strong>&ldquo;I don&rsquo;t need the calls&rdquo;:</strong> Ok no problem, the program is totally optional. It is just something that {doctor} feels will help them stay in touch with his/her patients better. They have many patients that they only sees once or twice a year, so this helps him/her to have more contact and keep up with how you&rsquo;re doing. Then you also have that nurse available even after office hours.</p>

<p style="margin-left:80px"><strong>If positive:</strong> (Go up to &lsquo;Positive Response&rsquo; paragraph)</p>

<p style="margin-left:80px"><strong>If negative: </strong>I understand. The program is here long term so if you ever want to use it again down the road just let us know and we can add you back on the list.I hope you have a great day!</p>

<p>&nbsp;</p>

<p style="margin-left:40px"><strong>&ldquo;I just call my doctor if I need anything, I don&rsquo;t need a nurse to call.&quot;</strong> {doctor} always has an open door policy, but because of the number of patients they have, they chose to invest in this program to ensure that each and every one of their patients receives the best care in-between visits. Our nurses can help you with a variety of things outside of the office, such as transportation, food, and other community resources. Would this be of value to you?</p>

<p style="margin-left:80px"><strong>If yes: </strong>(Go up to &lsquo;Positive Response&rsquo; paragraph)</p>

<p style="margin-left:80px"><strong>If no: </strong>We understand that this personalized program is not a right fit for everyone based upon various factors. The program is here long term so if you ever want to use it again down the road just let us know and we can add you back on the list.I hope you have a great day!</p>

<p>&nbsp;</p>

<p style="margin-left:40px"><strong>Can the nurse call me every other month or every 2-3 months instead of every month? </strong>Our nurses can call every other month - would that work for you?</p>

<p style="margin-left:80px"><strong>If yes: </strong>(Go up to &lsquo;Positive Response&rsquo; paragraph)</p>

<p style="margin-left:80px"><strong>If no:</strong> We understand that this personalized program is not a right fit for everyone based upon various factors. I do want to thank you for your time today - have a great day!</p>',
            ],
        ]);
    }
}
