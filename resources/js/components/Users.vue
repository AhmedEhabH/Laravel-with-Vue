<template>
  <div class="container">
    
    <!-- /.row -->
    <div class="row mt-5" v-if="$gate.isAdminOrAuthor()">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Users Table</h3>

            <div class="card-tools">
              <button class="btn btn-success" @click="newModal">
                Add New User
                <i class="fa fa-user-plus fa-fw"></i>
              </button>
            </div>
          </div>
          <!-- /.card-header -->
          <div class="card-body table-responsive p-0">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Type</th>
                  <th>Registed At</th>
                  <th>Modify</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in users" v-bind:key="user.id">
                  <td>{{ user.id }}</td>
                  <td>{{ user.name }}</td>
                  <td>{{ user.email }}</td>
                  <td>{{ user.type | upperFirstLetter }}</td>
                  <td>{{ user.created_at | myDate }}</td>
                  <td>
                    <a href="#" @click="editModal(user)">
                      <i class="fa fa-edit btn btn-primary"></i>
                    </a>
                    <a href="#" @click="deleteUser(user.id)">
                      <i class="fa fa-trash btn btn-danger"></i>
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
    </div>
    <!-- /.row -->

    <div v-if="!$gate.isAdminOrAuthor()">
      <not-found></not-found>
    </div>

    <!-- Modal -->
    <div
      class="modal fade"
      id="addNew"
      tabindex="-1"
      role="dialog"
      aria-labelledby="addNewLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 v-show="editMode" class="modal-title" id="addNewLabel">Update User's info</h5>
            <h5 v-show="!editMode" class="modal-title" id="addNewLabel">Add New User</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <form @submit.prevent="editMode ? updateUser() : createUser()">
            <!-- /.Modal Body -->
            <div class="modal-body">
              <div class="form-group">
                <input
                  v-model="form.name"
                  type="text"
                  name="name"
                  placeholder="Name"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('name') }"
                />
                <has-error :form="form" field="name"></has-error>
              </div>

              <div class="form-group">
                <input
                  v-model="form.email"
                  type="email"
                  name="email"
                  placeholder="Email"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('email') }"
                />
                <has-error :form="form" field="email"></has-error>
              </div>

              <div class="form-group">
                <textarea
                  v-model="form.bio"
                  name="bio"
                  id="bio"
                  placeholder="Short bio for user (Optional)"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('bio') }"
                ></textarea>
                <has-error :form="form" field="bio"></has-error>
              </div>

              <div class="form-group">
                <select
                  v-model="form.type"
                  name="type"
                  id="type"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('type') }"
                >
                  <option value>Select User Role</option>
                  <option value="admin">Admin</option>
                  <option value="user">Standard User</option>
                  <option value="author">Author</option>
                </select>
                <has-error :form="form" field="type"></has-error>
              </div>

              <div class="form-group">
                <input
                  v-model="form.password"
                  type="password"
                  name="password"
                  id="password"
                  placeholder="password"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('password') }"
                />
                <has-error :form="form" field="password"></has-error>
              </div>
            </div>
            <!-- /.Modal Body -->

            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
              <button v-show="editMode" type="submit" class="btn btn-success">Update</button>
              <button v-show="!editMode" type="submit" class="btn btn-primary">Create</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
  </div>
</template>

<script>
export default {
  data() {
    
    return {
      editMode: false,
      users: {},
      form: new Form({
        id:"",
        name: "",
        email: "",
        password: "",
        type: "",
        bio: "",
        photo: ""
      })
    };
  },

  methods: {
    newModal(){
      this.form.reset();
      this.form.clear();
      $("#addNew").modal("show");
      this.editMode = false;
    },

    editModal(user){
      this.editMode = true;
      this.form.reset();
      $("#addNew").modal("show");
      this.form.fill(user);
    },

    showToast(icon, title) {
      Toast.fire({
        icon: icon,
        title: title
      });
    },

    createUser() {
      this.$Progress.start();
      this.form
        .post("api/user")
        .then(({ data }) => {
          Fire.$emit('loadUsers');
          this.showToast("success", "Updated user successfully");
          $("#addNew").modal("hide");
        })
        .catch(({ error }) => {
          this.showToast("error", "Failed to update user");
          this.$Progress.fail();
        });
      
      this.$Progress.finish();
    },

    updateUser(id){
      this.$Progress.start();
      // console.log("Update...");
      this.form
      .put("api/user/"+this.form.id)
      .then((data) => {
        Fire.$emit('loadUsers');
        this.showToast("success", "Updated user successfully");
        $("#addNew").modal("hide");
      })
      .catch((error) => {
        this.showToast("error", "Failed to update user");
        this.$Progress.fail();
      });
      this.$Progress.finish();
    },

    loadUsers() {
      // this.$gate.isAdmin() || this.$gate.isAuthor()
      if(this.$gate.isAdminOrAuthor()){
        axios.get("api/user").then(({ data }) => (this.users = data.data));
      }
      
    },
    deleteUser(id) {
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.value) {
          this.form.delete('api/user/'+id).then(()=>{
            Fire.$emit('loadUsers');
            Swal.fire(
              'Deleted!',
              'Your file has been deleted.',
              'success'
            )
          })
          .catch(()=>{
            this.showToast('error', "Can't delete user");
          })
        }
      })
    },
  },

  mounted() {
    // console.log('Component mounted.')
  },

  created() {
    this.loadUsers();
    // setInterval(this.loadUsers, 5000);
    Fire.$on('loadUsers', ()=>{
      this.loadUsers();
    });
  }
};
</script>
