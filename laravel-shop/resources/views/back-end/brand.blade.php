@extends('back-end.components.master')
@section('contens')

      {{-- Modal create start --}}
      @include('back-end.messages.brand.create')
      {{-- Modal create end --}}

      {{-- Modal edit start --}}
      @include('back-end.messages.brand.edit')
      {{-- Modal edit start --}}

      <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Brands</h3>
                <p data-bs-toggle="modal" data-bs-target="#modalCreateBrand" class="card-description btn btn-primary ">new brand</p>
            </div>
            <table class="table table-striped">
              <thead>
                <tr> 
                  <th>Brand ID</th>
                  <th>Name</th>
                  <th>Category</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody class="brand_list">
                 {{-- <tr>
                    <td>B001</td>
                    <td>Vivo</td>
                    <th>Phone</th>
                    <th>
                        <span class="badge badge-success p-1">Active</span>
                        <span class="badge badge-danger  p-1">Inactive</span>
                    </th>
                    <th>
                        <button type="button" class=" btn btn-info  btn-sm" data-bs-toggle="modal" data-bs-target="#modalUpdateBrand">Edit</button>
                        <button type="button" class="btn btn-danger btn-sm">Delete</button>
                    </th>
                 </tr> --}}
              </tbody>

            </table>
            <div class="d-flex justify-content-between align-items-center">

                <div class="show-page mt-3">

                </div>

                <button onclick="BrandRefresh()" class=" btn btn-outline-danger rounded-0 btn-sm">refresh</button>

            </div>
          </div>
        </div>
      </div>
@endsection

@section('scripts')
<script>


    //Brand List
    const BrandList = (page=1,search='') => {
      $.ajax({
        type: "POST",
        url: "{{ route('brand.list') }}",
        data : {
            "page" : page,
            "search" : search
        },
        dataType: "json",
        success: function (response) {
            if(response.status == 200){
                let brands = response.brands;
                let tr = ``;
                $.each(brands, function (key,value) { 
                    tr += `
                    <tr>
                        <td>B${value.id}</td>
                        <td>${value.name}</td>
                        <th>${value.category.name}</th>
                        <th>
                            ${(value.status == 1) ? '<span class="badge badge-success p-1">Active</span>' : ' <span class="badge badge-danger  p-1">Inactive</span>' }
                        </th>
                        <th>
                            <button type="button" onclick="BrandEdit(${value.id},'${value.name}')" class=" btn btn-info  btn-sm" data-bs-toggle="modal" data-bs-target="#modalUpdateBrand">Edit</button>
                            <button type="button" onclick="BrandDelete(${value.id})" class="btn btn-danger btn-sm">Delete</button>
                        </th>
                    </tr>
                    `;
                });

                $(".brand_list").html(tr);

                //pagination
                let page = ``;
                let totalPage = response.page.totalPage;
                let currentPage = response.page.currentPage;
                page = `
                <nav aria-label="Page navigation example">
                    <ul class="pagination">
                        <li onclick="PreviousPage(${currentPage})" class="page-item ${(currentPage == 1) ? 'd-none' : 'd-block' }">
                        <a class="page-link" href="javascript:void()" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                        </li>`;

                        for(let i=1;i<=totalPage;i++){
                            page += `
                                <li onclick="BrandPage(${i})" class="page-item ${(i == currentPage) ? 'active' : '' }">
                                    <a class="page-link" href="javascript:void()">${i}</a>
                                </li>`;
                        }

                        page +=`<li onclick="NextPage(${currentPage})" class="page-item ${( currentPage == totalPage ) ? 'd-none' : 'd-block'}">
                        <a class="page-link" href="javascript:void()" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                        </li>
                    </ul>
                </nav>
                `;

                if(totalPage > 1 ){
                    $(".show-page").html(page);
                }

            }
        }
      });
    }

    //Calling function
    BrandList();

    //Brand Refresh page
    const BrandRefresh = () => {
        BrandList();
        $("#searchBox").val(" ");
    }


    //search event 
    $(document).on("click",'.searchBtn', function () {
         let searchValue = $("#searchBox").val();
         BrandList(1,searchValue);
         
         //close modal
         $("#modalSearch").modal('hide');
    });



    //Pagination
    const BrandPage = (page) => {
        BrandList(page);
    }

    //Previous Page
    const NextPage  = (page) => {
        BrandList(page + 1);
    }

    //Previous Page
    const PreviousPage = (page) => {
        BrandList(page - 1);
    }


   //Brand Delete
   const BrandDelete = (id) => {
      if(confirm("Do you want to delete this ?")){
        $.ajax({
            type: "POST",
            url: "{{ route('brand.destroy') }}",
            data: {
                "id" : id
            },
            dataType: "json",
            success: function (response) {
                if(response.status == 200){
                    BrandList();
                    Message(response.message);
                }
            }
        });
      }
   }

   //Brand Edit
    const BrandEdit = (id,name) => {
       $(".name_edit").val(name);
       $("#color_id").val(id);
    }

   //Brand Update
   const BrandUpdate = (form) => {
    let payloads = new FormData($(form)[0]);
       $.ajax({
        type: "POST",
        url: "{{ route('brand.update') }}",
        data: payloads,
        dataType: "json",
        contentType: false,
        processData: false,
        success: function (response) {
            if(response.status == 200){
                $("#modalUpdateBrand").modal("hide");
                $(form).trigger('reset');
                $(".name").removeClass("is-invalid").siblings("p").removeClass("text-danger").text(" ");
                Message(response.message);
                BrandList();
            }else{
                let error = response.error;
                $(".name").addClass("is-invalid").siblings("p").addClass("text-danger").text(error.name);
            }
        }
       });
   }

   const BrandStore = (form) => {
       let payloads = new FormData($(form)[0]);
       $.ajax({
        type: "POST",
        url: "{{ route('brand.store') }}",
        data: payloads,
        dataType: "json",
        contentType: false,
        processData: false,
        success: function (response) {
            if(response.status == 200){
                $("#modalCreateBrand").modal("hide");
                $(form).trigger('reset');
                $(".name").removeClass("is-invalid").siblings("p").removeClass("text-danger").text(" ");
                Message(response.message);
                BrandList();
            }else{
                let error = response.error;
                $(".name").addClass("is-invalid").siblings("p").addClass("text-danger").text(error.name);
            }
        }
       });
   }


</script>
@endsection