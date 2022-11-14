<script setup>
  import { ref, computed, onMounted, inject } from 'vue'
  import {useRouter} from 'vue-router'
  import ProjectTable from "./ProjectTable.vue"

  const router = useRouter()

  const axios = inject("axios")
  const toast = inject("toast")

  const loadProjects = () => {
      axios.get('projects')
        .then((response) => {
          projects.value = response.data.data
        })
        .catch((error) => {
          console.log(error)
        })
  }
  
  const loadUsers = () => {
      axios.get('users')
        .then((response) => {
          users.value = response.data.data
        })
        .catch((error) => {
          console.log(error)
        })
  }

  const addProject = () => {
    router.push({ name: 'NewProject'})
  }
  
  const editProject = (project) => {
    router.push({ name: 'Project', params: { id: project.id } })
  }

  const deleteProjectConfirmed = () => {
    axios.delete('projects/' + projectToDelete.value.id)
        .then((response) => {
          const deletedProject = response.data.data
          const idx = projects.value.findIndex((t) => t.id === deletedProject.id)
          if (idx >= 0) {
            projects.value.splice(idx, 1)
          }
          toast.info("Project " + projectToDeleteDescription.value + " was deleted")
        })
        .catch((error) => {
          toast.error("It was not possible to delete Project " + projectToDeleteDescription.value + "!")
        })

  }
  const clickToDeleteProject = (project) => {
    projectToDelete.value = project
    deleteConfirmationDialog.value.show()
  }


  const projects = ref([])
  const projectToDelete = ref(null)
  const users = ref([])
  const filterByResponsibleId = ref(null)
  const filterByStatus = ref('W')
  const deleteConfirmationDialog = ref(null)

  const filteredProjects = computed(()=>{
    return projects.value.filter(p =>
        (!filterByResponsibleId.value
          || filterByResponsibleId.value == p.responsible_id
        ) &&
        (!filterByStatus.value
          || filterByStatus.value == p.status
        ))
  })

  const totalProjects = computed(()=>{
    return projects.value.reduce((c, p) =>
        (!filterByResponsibleId.value
          || filterByResponsibleId.value == p.responsible_id
        ) &&
          (!filterByStatus.value
            || filterByStatus.value == p.status
          ) ? c + 1 : c, 0)
  })

  const projectToDeleteDescription = computed(() => {
    return projectToDelete.value
    ? `#${projectToDelete.value.id} (${projectToDelete.value.name})`
    : ""
  })

  onMounted(() => {
    loadUsers()
    loadProjects()
  })

</script>

<template>
  <confirmation-dialog
    ref="deleteConfirmationDialog"
    confirmationBtn="Delete task"
    :msg="`Do you really want to delete the task ${projectToDeleteDescription}?`"
    @confirmed="deleteProjectConfirmed"
  >
  </confirmation-dialog>

  <div class="d-flex justify-content-between">
    <div class="mx-2">
      <h3 class="mt-4">Projects</h3>
    </div>
    <div class="mx-2 total-filtro">
      <h5 class="mt-4">Total: {{ totalProjects }}</h5>
    </div>
  </div>
  <hr>
  <div class="mb-3 d-flex justify-content-between flex-wrap">
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label
        for="selectStatus"
        class="form-label"
      >Filter by status:</label>
      <select
        class="form-select"
        id="selectStatus"
        v-model="filterByStatus"
      >
        <option :value="null"></option>
        <option value="P">Pending</option>
        <option value="W">Work In Progress</option>
        <option value="C">Completed</option>
        <option value="I">Interrupted</option>
        <option value="D">Discarded</option>
      </select>
    </div>
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label
        for="selectOwner"
        class="form-label"
      >Filter by owner:</label>
      <select
        class="form-select"
        id="selectOwner"
        v-model="filterByResponsibleId"
      >
        <option :value="null"></option>
        <option
          v-for="user in users"
          :key="user.id"
          :value="user.id"
        >{{user.name}}</option>
      </select>
    </div>
    <div class="mx-2 mt-2">
      <button
        type="button"
        class="btn btn-success px-4 btn-addprj"
        @click="addProject"
      ><i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Project</button>
    </div>
  </div>
  <project-table
    :projects="filteredProjects"
    :showId="true"
    :showDates="true"
    @edit="editProject"
    @delete="clickToDeleteProject"
  ></project-table>
</template>

<style scoped>
.filter-div {
  min-width: 12rem;
}
.total-filtro {
  margin-top: 0.35rem;
}
.btn-addprj {
  margin-top: 1.85rem;
}
</style>
