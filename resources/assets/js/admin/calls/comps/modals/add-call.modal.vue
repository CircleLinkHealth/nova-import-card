<template>
    <modal name="add-call" :info="addCallModalInfo" :no-footer="true" class-name="modal-add-call">
      <template slot="title"><div>Add New Call</div></template>
      <template scope="props">
        <div class="row">
          <div class="col-sm-12">
            <div class="row form-group">
              <div class="col-sm-5">
                Patient:
              </div>
              <div class="col-sm-7">
                <select class="form-control">
                  <option value="">Unassigned</option>
                </select>
              </div>
            </div>
            <div class="row form-group">
              <div class="col-sm-5">
                Nurse:
              </div>
              <div class="col-sm-7">
                <select class="form-control">
                  <option value="">Unassigned</option>
                </select>
              </div>
            </div>
            <div class="row form-group">
              <div class="col-sm-5">
                Date:
              </div>
              <div class="col-sm-7">
                <input class="form-control" type="date" />
              </div>
            </div>
            <div class="row form-group">
              <div class="col-sm-5">
                Start Time
              </div>
              <div class="col-sm-7">
                <input class="form-control" type="time" />
              </div>
            </div>
            <div class="row form-group">
              <div class="col-sm-5">
                End Time:
              </div>
              <div class="col-sm-7">
                <input class="form-control" type="time" />
              </div>
            </div>
            <div class="row form-group">
              <div class="col-sm-5">
                Add Text:
              </div>
              <div class="col-sm-7">
                <textarea class="form-control"></textarea>
              </div>
            </div>
          </div>
        </div>
      </template>
    </modal>
</template>

<script>
    import { Event } from 'vue-tables-2'
    import Modal from '../modal'

    export default {
        name: 'add-call-modal',
        components: {
            'modal': Modal
        },
        data() {
            return {
                addCallModalInfo: {
                    okHandler() {
                        console.log("okay clicked")
                        Event.$emit("modal-add-call:hide")
                    }
                }
            }
        }
    }
</script>

<style>
    .modal-add-call .modal-container {
        width: 420px;
    }
</style>