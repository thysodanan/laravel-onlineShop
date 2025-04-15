@extends('back-end.components.master')
@section('contens')

      {{-- Modal create start --}}
      @include('back-end.messages.category.create')
      {{-- Modal create end --}}

      {{-- Modal edit start --}}
      @include('back-end.messages.category.edit')
      {{-- Modal edit start --}}

      <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Category</h3>
                <p data-bs-toggle="modal" data-bs-target="#modalCreateCategory" class="card-description btn btn-primary ">new category</p>
            </div>
            <table class="table table-striped">
              <thead>
                <tr> 
                  <th>Category ID</th>
                  <th>Image</th>
                  <th>Name</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody class="category_list">
                
              </tbody>

            </table>
          </div>
        </div>
      </div>
@endsection
@section('scripts')
    <script>

        const CategoryList = () => {
            $.ajax({
                type: "POST",
                url: "{{ route('category.list') }}",
                dataType: "json",
                success: function (response) {
                    if(response.status == 200){
                        let categories = response.categories;
                        console.log(categories)
                        let tr = ``;
                        $.each(categories,function (key,value) {
                             tr += `
                             <tr>
                                <td>CATE${value.id}</td>
                                <td>
                                    <img src="{{ asset('uploads/category/${value.image}') }}">
                                </td>
                                <td>${value.name}</td>
                                <td>
                                    ${ (value.status == 1) ? '<span class="bg-success text-light p-1 rounded-1">Active</span>' : 
                                                              '<span class="bg-danger text-light p-1 rounded-1">Block</span>'
                                    }
                                </td>
                                <td>
                                    <button data-bs-toggle="modal" data-bs-target="#modalUpdateCategory" type="button" onclick="CategoryEdit(${value.id})" class="btn btn-primary btn-sm mr-1">Edit</button>
                                    <button type="button" onclick="CategoryDestory(${value.id})" class="btn btn-danger btn-sm">Delete</button>
                                </td>
                             </tr>
                            `;
                        });
                        $(".category_list").html(tr); 
                    }
                }
            });
        }

        CategoryList();

        const UploadImage = (form) => {
            let payloads = new FormData($(form)[0]);
            $.ajax({
                type: "POST",
                url: "{{ route('category.upload') }}",
                data: payloads,
                dataType: "json",
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response.status == 200){
                        let img = `
                            <input type="hidden" name="category_image" value="${response.image}">
                            <img style="width:400px;" src="{{ asset('uploads/temp/${response.image}') }}" >
                            <button type="button" onclick="CancelImage('${response.image}')" class="btn btn_cancle btn-danger rounded-0 btn-sm">cancel</button>
                        `;

                        $(".show-image-category").html(img);

                        // $(form).trigger("reset");
                        $(".image").val("");
                        $(".image").removeClass("is-invalid").siblings('p').removeClass("text-danger").text(" ");
                    }else{
                        $('.image').addClass("is-invalid").siblings('p').addClass("text-danger").text(response.error.image);
                    }
                }
            });
        }

        const CancelImage = (img) => {
            if(confirm("Do you want to cancel image?")){
                $.ajax({
                    type: "POST",
                    url: "{{ route('category.cancel') }}",
                    data: {
                        "image" : img
                    },
                    dataType: "json",
                    success: function (response) {
                        if(response.status == 200){
                            $(".show-image-category").html("");
                            Message(response.message);
                        }
                    }
                });
            }
        }

        const CategoryEdit = (id) => {
            $.ajax({
                type: "POST",
                url: "{{ route('category.edit') }}",
                data: {
                    "id" : id
                },
                dataType: "json",
                success: function (response) {
                    if(response.status == 200){
                        $(".name_edit").val(response.category.name);
                        $("#category_id").val(response.category.id);
                        $(".show-image-category-edit").html(" ");
                        if(response.category.image != null){
                            let img = `
                               <input type="hidden" name="cate_old_image" value="${response.category.image}">
                               <img style="width:400px;" src="{{ asset('uploads/category/${response.category.image}') }}">
                            `;
                            $(".show-image-category-edit").html(img);
                         
                        }
                    }
                }
            });
        }

        const StoreCategory = (form) => {
            let payloads = new FormData($(form)[0]);
            $.ajax({
                type: "POST",
                url: "{{ route('category.store') }}",
                data: payloads,
                dataType: "json",
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response.status == 200){
                        $("#modalCreateCategory").modal("hide");
                        $(form).trigger("reset");
                        $(".name").removeClass("is-invalid").siblings("p").removeClass("text-danger").text("")
                        $(".show-image-category").html("");
                        Message(response.message);
                        CategoryList();
                    }else{
                        $(".name").addClass("is-invalid").siblings("p").addClass("text-danger").text(response.error.name);
                    }
                }
            });
        }

        const UpdateCategory = (form) => {
            let payloads = new FormData($(form)[0]);
            $.ajax({
                type: "POST",
                url: "{{ route('category.update') }}",
                data: payloads,
                dataType: "json",
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response.status == 200){
                        $("#modalUpdateCategory").modal("hide");
                        $(form).trigger("reset");
                        $(".name").removeClass("is-invalid").siblings("p").removeClass("text-danger").text("")
                        $(".show-image-category-edit").html("");
                        Message(response.message);
                        CategoryList();
                    }else{
                        $(".name").addClass("is-invalid").siblings("p").addClass("text-danger").text(response.error.name);
                    }
                }
            });
        }

        const CategoryDestory = (id) => {
            if(confirm("Do you want to delete this ?")){
                $.ajax({
                    type: "POST",
                    url: "{{ route('category.destroy') }}",
                    data: {
                        "id" : id
                    },
                    dataType: "json",
                    success: function (response) {
                        if(response.status == 200){
                            CategoryList();
                            Message(response.message);
                        }
                    }
                });
            }
        }
    </script>
@endsection