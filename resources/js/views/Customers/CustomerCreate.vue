<template>
  <div class="customer-create">
    <div class="max-w-4xl mx-auto py-6">
      <div class="bg-white shadow rounded-lg p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Create New Customer</h1>
        
        <!-- Placeholder form -->
        <div class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700">First Name</label>
              <input 
                type="text" 
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter first name"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Last Name</label>
              <input 
                type="text" 
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter last name"
              />
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input 
              type="email" 
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter email address"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700">Phone</label>
            <input 
              type="tel" 
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter phone number"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700">Address</label>
            <textarea 
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              rows="3"
              placeholder="Enter address"
            ></textarea>
          </div>
        </div>
        
        <!-- Placeholder buttons -->
        <div class="mt-8 flex justify-end space-x-3">
          <button 
            type="button"
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            @click="$router.push('/customers')"
          >
            Cancel
          </button>
          <button 
            type="submit"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Create Customer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Placeholder component - will be implemented with actual functionality later
</script> 