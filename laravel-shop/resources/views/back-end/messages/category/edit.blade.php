<div class="modal fade" id="modalUpdateCategory" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width:40%;">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Update Category</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
           <form method="POST" id="formUpdateCategory" enctype="multipart/form-data">

                <div class="form-group">
                    <input type="hidden" id="category_id" name="category_id">
                   <label for="">Category Name</label>
                   <input type="text" name="name" class="name name_edit form-control" id="">
                   <p></p>
                </div>

                <div class="form-group">
                  <label for="">Image</label>
                  <input type="file" name="image" class="image form-control" id="">
                  <p></p>
                  <button type="button" onclick="UploadImage('#formUpdateCategory')" class="btn btn_upload btn-success rounded-0">update</button>
                </div>

                <div class="show-image-category show-image-category-edit">

                </div>
                

                <div class="form-group">
                  <label for="">Status</label>
                  <select name="status" class="status form-control">
                     <option value="1">Active</option>
                     <option value="0">Block</option>
                  </select>
                  <p></p>
                </div>

           </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" onclick="UpdateCategory('#formUpdateCategory')" class="btn btn-primary">Update</button>
        </div>
      </div>
    </div>
</div>