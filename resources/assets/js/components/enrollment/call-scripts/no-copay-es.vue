<template>
    <div>
        <p>
            <b>Speak clearly and cheerfully</b>: Hola, {{ name }} estoy llamando en nombre de
            los doctores Dr. {{providerFullName}} de la {{practiceName}}. Cómo estás?
        </p>

        <p>
            Soy bien. Los médicos le han invitado a su nuevo programa de gestión de atención
            personalizada y es posible que
            haya recibido una carta a este efecto.
        </p>

        <p>Dr. {{providerFullName}} piensan que este programa sería muy útil para usted y le
            gustaría
            que se inscribiera.
            Permítame contarle algo sobre este programa y cómo puede ayudarle a mantenerse
            saludable?</p>

        <p>
            <b>
                [patients interested usually discuss their conditions here.. be sure to listen​] [if
                patient asks where
                calling
                from, use the practice name in google sheet, e.g., CCN or Ferguson]
            </b>
        </p>

        <p>Este es un nuevo programa de atención preventiva de Medicare para ayudarlo a usted y a su
            médico a
            cuidar mejor de su salud. Medicare ha decidido que su enfoque de la medicina necesitaba
            mejoras.
            Antes, Medicare era reactivo, a menudo esperaba hasta que los pacientes terminaban en el
            hospital
            para proporcionarles atención médica, no era bueno para los pacientes y caro para
            Medicare.</p>

        <p>Ahora, Medicare está siendo proactivo: proporcionando atención entre las visitas al
            médico para
            asegurarse que sus afecciones están bajo control, está tomando sus medicamentos y no
            tiene ningún
            síntoma que pueda estar molestándolo.</p>

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

        <p><b>[patients interested usually discuss their conditions here.. be sure to listen​] [if
            patient asks where
            calling
            from, use the practice name in google sheet, e.g., CCN or Ferguson]</b></p>

        <p><i>[Ambassador: Please click the appropriate button based on patient’s
            answer and follow instructions in subsequent popup forms. Thank you!]</i></p>
    </div>
</template>

<script>
    export default {
        name: "no-copay-es",
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