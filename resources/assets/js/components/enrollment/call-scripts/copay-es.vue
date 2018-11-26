<template>
    <div>
        <p><b>Speak clearly and cheerfully</b>: Hola, {{ name }} estoy llamando en nombre de
            los doctores <b>Dr. {{providerFullName}}</b> de la {{practiceName}}. Los médicos le han
            invitado a su nuevo
            programa de
            gestión de atención personalizada y es posible que haya recibido una carta a este
            efecto.</p>

        <p><b>Dr. {{providerFullName}}</b> piensan que este programa sería muy útil para usted y le
            gustaría que se
            inscribiera.
            Permítame contarle algo sobre este programa y cómo puede ayudarle a mantenerse
            saludable.</p>

        <p>
            <b>
                [patients interested usually discuss their conditions here.. be sure to listen​] [if
                patient asks where
                calling
                from, use the practice name in google sheet, e.g., CCN or Ferguson]
            </b>
        </p>

        <p>Este es un nuevo programa de atención preventiva de Medicare para ayudarlo a usted y a su
            médico a cuidar mejor
            de su
            salud. Medicare ha decidido que su enfoque de la medicina necesitaba mejoras. Antes,
            Medicare era reactivo, a
            menudo
            esperaba hasta que los pacientes terminaban en el hospital para
            proporcionarles atención médica, no era bueno para los pacientes y caro para Medicare.
            Ahora, Medicare está
            siendo
            proactivo: proporcionando atención entre las visitas al médico para asegurarse que sus
            afecciones están bajo
            control, está tomando sus medicamentos y no tiene ningún síntoma que pueda estar
            molestándolo.</p>

        <p>Ahora, Medicare está siendo proactivo: proporcionando atención entre las visitas al
            médico para
            asegurarse que sus afecciones están bajo control, está tomando sus medicamentos y no
            tiene ningún
            síntoma que pueda estar molestándolo.</p>

        <p>Es un programa gratuito si usted está en Medicaid o tiene un seguro suplementario.Si no,
            hay un copago de
            alrededor
            de $ 8 por mes. Recuerde, este servicio puede ahorrarle visitas a Atención de Urgencia o
            a la consulta del Dr.
            conectándole a una enfermera. El valor entregado está muy por encima de los $ 8 por
            mes.</p>

        <p>He aquí algunos detalles sobre el programa:</p>

        <p>
        <li>Un encargado de cuidado personal, una enfermera registrada, le hará una rápida llamada
            telefónica dos veces al
            mes,
            para brindarle apoyo, atención personalizada y para ver cómo está usted
        </li>

        <li> También puede dejarnos un mensaje las 24 horas los 7 días de la semana y uno de
            nuestros encargados de atención
            se
            pondrá en contacto con usted en un tiempo razonable
        </li>

        <li> Puede retirarse del programa en cualquier momento que desee. Solo llámenos.</li>

        <li> Solamente puedes ser parte de un programa de cuidados del Doctor a la vez</li>

        <p>¿Puede informarle a su doctor que usted aceptó inscribirse en este programa? (Recuerde
            que siempre
            puede retirarse si no le gusta) </p>

        <p>
            <b>[Si el paciente acepta]</b>
            ¡Estupendo!
            1-¿Quiere quele llamemos directamente o hay alguien más con el cual quiere que nos
            pongamos
            en contacto?
            2- [Confirme el mejor N° de contacto del paciente, los tiempos preferidos para llamarlo]
            3-Una enfermera registrada le llamará en breve del mismo desde el cual lo estoy llamando
            [number of practice]. Por favor, guárdelo para que acepte la llamada cuando suene el
            teléfono.
            ¡Me alegro de haberme conectado! ¡Que tenga un muy buen día!
        </p>

        <p><b>[Si el paciente no acepta, tenga en cuenta esto]:</b> Gracias por su tiempo y le
            informaremos a su
            médico</p>

        <p><i>[Ambassador: Please click the appropriate button based on patient’s
            answer and follow instructions in subsequent popup forms. Thank you!]</i></p>
    </div>
</template>

<script>
    export default {
        name: "copay-es",
        props: [
            'name',
            'userFullName',
            'providerFullName',
            'practiceName'
        ]
    }
</script>

<style scoped>

</style>