<template>
    <modal name="select-nurse" :no-title="true" :no-footer="true" :info="selectNursesModalInfo">
      <template scope="props">
        <select class="form-control" @change="props.info.onChange">
          <option value="">Pick a Nurse</option>
          <option value="1">Nurse N RN</option>
          <option value="2">Kathryn Alchalabi RN</option>
          <option value="3">atricia Koeppel RN</option>
          <option value="4">Dillenis Diaz RN</option>
          <option value="5">Liza Herrera RN</option>
          <option value="6">Monique Potter RN</option>
          <option value="7">Nurse Loisa</option>
        </select>
      </template>
    </modal>
</template>

<script>
    import Modal from '../modal'

    export default {
        name: 'select-nurse-modal',
        components: {
            'modal': Modal
        },
        data() {
            return {
                selectNursesModalInfo: {
                    onChange(e) {
                    console.log(e)
                    }
                }
            }
        }
    }
</script>

<style>
    
</style>