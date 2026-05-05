<div id="showMedicine" class="modal fade side-fade" role="dialog" aria-hidden="true">
       <div class="modal-dialog modal-lg">
              <!-- Modal content-->
              <div class="modal-content">
                     <div class="modal-header">
                            <h2>{{ __('messages.medicine.medicine_details') }}</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                   aria-label="Close"></button>
                     </div>
                     <div class="modal-body">
                            <div class="row">
                                   <div class="form-group col-lg-6 mb-5">
                                          <label for="medicine_name"
                                                 class="pb-2 fs-5 text-gray-600">{{ __('messages.medicine.medicine').(':') }}</label><br>
                                          <span id="showMedicineName"
                                                 class="fs-5 text-gray-800 showSpan"></span>
                                   </div>
                                   <div class="form-group col-lg-6 mb-5">
                                          <label for="medicine_brand"
                                                 class="pb-2 fs-5 text-gray-600">{{ __('Brand Name').(':') }}</label><br>
                                          <span id="showMedicineBrand"
                                                 class="fs-5 text-gray-800 showSpan"></span>
                                   </div>
                                   <div class="form-group col-lg-6 mb-5">
                                          <label for="medicine_generic"
                                                 class="pb-2 fs-5 text-gray-600">{{ __('messages.medicine.generic').(':') }}</label><br>
                                          <span id="showMedicineGeneric"
                                                 class="fs-5 text-gray-800 showSpan"></span>
                                   </div>
                                   <div class="form-group col-lg-6 mb-5">
                                          <label for="medicine_category"
                                                 class="pb-2 fs-5 text-gray-600">{{ __('messages.medicine.category').(':') }}</label><br>
                                          <span id="showMedicineCategory"
                                                 class="fs-5 text-gray-800 showSpan"></span>
                                   </div>
                                   <div class="form-group col-lg-6 mb-5">
                                          <label for="showMedicineDosage"
                                                 class="pb-2 fs-5 text-gray-600">{{ __('messages.medicine_availability.dosage').(':') }}</label><br>
                                          <span id="showMedicineDosage"
                                                 class="fs-5 text-gray-800 showSpan"></span>
                                   </div>
                                   <div class="form-group col-lg-6 mb-5">
                                          <label for="showMedicineUom"
                                                 class="pb-2 fs-5 text-gray-600">{{ __('Unit of Measure').(':') }}</label><br>
                                          <span id="showMedicineUom"
                                                 class="fs-5 text-gray-800 showSpan"></span>
                                   </div>
                                   <div class="form-group col-lg-6 mb-5">
                                          <label for="showMedicineSku"
                                                 class="pb-2 fs-5 text-gray-600">{{ __('SKU').(':') }}</label><br>
                                          <span id="showMedicineSku"
                                                 class="fs-5 text-gray-800 showSpan"></span>
                                   </div>
                                   <div class="form-group col-lg-6 mb-5">
                                          <label for="showMedicineReorderLevel"
                                                 class="pb-2 fs-5 text-gray-600">{{ __('Reorder Level').(':') }}</label><br>
                                          <span id="showMedicineReorderLevel"
                                                 class="fs-5 text-gray-800 showSpan"></span>
                                   </div>
                                   <div class="form-group col-lg-6 mb-5">
                                          <label for="showMedicineQuanity"
                                                 class="pb-2 fs-5 text-gray-600">{{ __('messages.medicine.quantity').(':') }}</label><br>
                                          <span id="showMedicineQuanity"
                                                 class="fs-5 text-gray-800 showSpan"></span>
                                   </div>
                                   <div class="form-group col-lg-6 mb-5">
                                          <label for="showMedicineAvailableQuanity"
                                                 class="pb-2 fs-5 text-gray-600">{{ __('messages.medicine.available_quantity').(':') }}</label><br>
                                          <span id="showMedicineAvailableQuanity"
                                                 class="fs-5 text-gray-800 showSpan"></span>
                                   </div>
                                   <div class="form-group col-lg-6 mb-5">
                                          <label for="created_on"
                                                 class="pb-2 fs-5 text-gray-600">{{ __('messages.web.created_at').(':') }}</label><br>
                                          <span id="showMedicineCreatedOn"
                                                 class="fs-5 text-gray-800 showSpan"></span>
                                   </div>
                                   <div class="form-group col-lg-6 mb-5">
                                          <label for="updated_on"
                                                 class="pb-2 fs-5 text-gray-600">{{ __('messages.patient.last_updated').(':') }}</label><br>
                                          <span id="showMedicineUpdatedOn"
                                                 class="fs-5 text-gray-800 showSpan"></span>
                                   </div>
                                   <div class="form-group col-lg-6 mb-5">
                                          <label for="description"
                                                 class="pb-2 fs-5 text-gray-600">{{ __('messages.medicine.description').(':') }}</label><br>
                                          <span id="showMedicineDescription"
                                                 class="fs-5 text-gray-800 showSpan"></span>
                                   </div>

                                   <!-- Dosage and Quantity Table -->
                                   <div class="col-12 mt-5">
                                          <h4 class="mb-3">{{ __('Available Stock by Dosage') }}</h4>
                                          <div class="table-responsive">
                                                 <table class="table table-striped table-bordered">
                                                        <thead class="thead-light">
                                                               <tr>
                                                                      <th>{{ __('messages.medicine_availability.dosage') }}</th>
                                                                      <th>{{ __('Available Quantity') }}</th>
                                                                      <th>{{ __('Expiry Date') }}</th>
                                                                      <th>{{ __('Remaining Days') }}</th>
                                                               </tr>
                                                        </thead>
                                                        <tbody id="showMedicineDosageTable">
                                                               <tr>
                                                                      <td colspan="4" class="text-center text-muted">{{ __('No data available') }}</td>
                                                               </tr>
                                                        </tbody>
                                                 </table>
                                          </div>
                                   </div>

                                   <!-- Expired Dosage and Quantity Table -->
                                   <div class="col-12 mt-5">
                                          <h4 class="mb-3">{{ __('Expired Stock by Dosage') }}</h4>
                                          <div class="table-responsive">
                                                 <table class="table table-striped table-bordered">
                                                        <thead class="thead-light">
                                                               <tr>
                                                                      <th>{{ __('messages.medicine_availability.dosage') }}</th>
                                                                      <th>{{ __('Expired Quantity') }}</th>
                                                                      <th>{{ __('Expiry Date') }}</th>
                                                                      <th>{{ __('Expired Since') }}</th>
                                                               </tr>
                                                        </thead>
                                                        <tbody id="showMedicineExpiredDosageTable">
                                                               <tr>
                                                                      <td colspan="4" class="text-center text-muted">{{ __('No expired stock') }}</td>
                                                               </tr>
                                                        </tbody>
                                                 </table>
                                          </div>
                                   </div>
                            </div>
                     </div>
              </div>
       </div>
</div>