<template>
  <div class="customer-show">
    <div class="max-w-4xl mx-auto py-6">
      <div class="bg-white shadow rounded-lg p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Customer Details</h1>
          <div class="flex space-x-3">
            <button
              type="button"
              class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              @click="$router.push('/customers')"
            >
              Back to List
            </button>
            <button
              type="button"
              class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Edit Customer
            </button>
          </div>
        </div>

        <!-- Customer Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
            <dl class="space-y-3">
              <div>
                <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                <dd class="text-sm text-gray-900">John Doe</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Email</dt>
                <dd class="text-sm text-gray-900">john.doe@example.com</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                <dd class="text-sm text-gray-900">(555) 123-4567</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="text-sm">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Active
                  </span>
                </dd>
              </div>
            </dl>
          </div>

          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Address Information</h3>
            <dl class="space-y-3">
              <div>
                <dt class="text-sm font-medium text-gray-500">Address</dt>
                <dd class="text-sm text-gray-900">123 Main Street</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">City</dt>
                <dd class="text-sm text-gray-900">Anytown</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">State</dt>
                <dd class="text-sm text-gray-900">CA</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">ZIP Code</dt>
                <dd class="text-sm text-gray-900">90210</dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Additional Sections -->
        <div class="mt-8">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
          <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-700">
              This is a placeholder for customer notes. Notes will be displayed here when implemented.
            </p>
          </div>
        </div>

        <!-- Related Data Placeholders -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-900 mb-2">Jobs</h4>
            <p class="text-sm text-gray-500">No jobs found</p>
          </div>

          <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-900 mb-2">Estimates</h4>
            <p class="text-sm text-gray-500">No estimates found</p>
          </div>

          <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-900 mb-2">Appointments</h4>
            <p class="text-sm text-gray-500">No appointments found</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Placeholder component - will be implemented with actual functionality later
</script>
