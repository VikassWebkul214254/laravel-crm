@props([
    'name'      => '',
    'aiEnabled' => true,
])

@php
    $aiEnabledAttr = $attributes->get('ai-enabled', 'true');

    $isAiEnabled = $aiEnabledAttr === 'true' || $aiEnabledAttr === true || $aiEnabledAttr === '1' || $aiEnabledAttr === 1;
@endphp

<div class="space-y-2">
    @if($isAiEnabled)
        <!-- AI Prompt Input -->
        <div class="flex items-center gap-2">
            <label for="ai-prompt-{{ $name }}" class="text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">
                Ask AI:
            </label>
            <input
                id="ai-prompt-{{ $name }}"
                type="text"
                class="w-full rounded border border-gray-200 mb-3 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                placeholder="e.g., Write a kid-related product description"
                v-model="aiPrompts['{{ $name }}']"
            >
        </div>
    @endif

    <div class="relative">
        <v-field
            v-slot="{ field, errors }"
            {{ $attributes->only(['name', ':name', 'value', ':value', 'v-model', 'rules', ':rules', 'label', ':label']) }}
            name="{{ $name }}"
        >
            @php
                $defaultAttributes = [
                    'class' => 'w-full rounded border border-gray-200 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400'
                ];

                if ($attributes->get('tinymce', false) || $attributes->get(':tinymce', false)) {
                    $defaultAttributes['id'] = $attributes->get(':id', 'id');
                }
            @endphp

            <textarea
                type="text"
                name="{{ $name }}"
                v-bind="field"
                :class="[errors.length ? 'border !border-red-600 hover:border-red-600' : '']"
                {{
                    $attributes
                        ->except(['value', ':value', 'v-model', 'rules', ':rules', 'label', ':label', 'aiEnabled', ':aiEnabled', 'ai-enabled', ':ai-enabled'])
                        ->merge($defaultAttributes)
                }}
            ></textarea>

            @if ($attributes->get('tinymce', false) || $attributes->get(':tinymce', false))
                <x-admin::tinymce
                    :selector="'textarea#' . ($attributes->get('id') ?? $attributes->get(':id'))"
                    ::field="field"
                />
            @endif
        </v-field>

        @if($isAiEnabled)
            <!-- AI Improve Button (Right side) -->
            <div class=" absolute ltr:left-2 rtl:right-2 bottom-4 z-10" style="left: 8px;">
                <button
                    type="button"
                    @click="improveText('{{ $name }}')"
                    class="primary-button flex items-center gap-1.5 text-xs"
                    :disabled="aiImproving ? true : false"
                    title="Improve existing text with AI"
                >
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>AI Improve</span>
                </button>
            </div>

            <!-- Get Answer Button (Left side) -->
            <div class="absolute ltr:right-2 rtl:left-2 bottom-4 z-10">
                <button
                    type="button"
                    @click="getAnswer('{{ $name }}')"
                    class="secondary-button flex items-center gap-1.5 text-xs"
                    :disabled="aiImproving ? true : false"
                    title="Get AI answer based on instruction"
                >
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <span>Get Answer</span>
                </button>
            </div>
        @endif
    </div>
</div>

@pushOnce('scripts')
    <script type="module">
        if (typeof window.textareaAiMixin === 'undefined') {
            window.textareaAiMixin = {
                data() {
                    return {
                        aiImproving: false,
                        aiResults: {},
                        aiPrompts: {}, // Store prompts for each field
                    };
                },

                methods: {
                    async improveText(fieldName) {
                        // Get the field value
                        const field = this.$refs[fieldName] || document.querySelector(`[name="${fieldName}"]`);
                        
                        if (! field) {
                            this.$emitter.emit('add-flash', { 
                                type: 'error', 
                                message: 'Field not found' 
                            });

                            return;
                        }

                        const text = field.value?.trim();

                        if (! text) {
                            this.$emitter.emit('add-flash', { 
                                type: 'warning', 
                                message: 'Please enter some text first' 
                            });

                            return;
                        }

                        if (text.length < 10) {
                            this.$emitter.emit('add-flash', { 
                                type: 'warning', 
                                message: 'Text must be at least 10 characters long' 
                            });

                            return;
                        }

                        if (text.length > 10000) {
                            this.$emitter.emit('add-flash', { 
                                type: 'warning', 
                                message: 'Text is too long. Maximum 10,000 characters allowed' 
                            });

                            return;
                        }

                        this.aiImproving = true;

                        try {
                            const response = await this.$axios.post('{{ route('ai.improve') }}', {
                                paragraph: text
                            });

                            if (response.data.success) {
                                // Show modal with comparison
                                this.showAiComparisonModal(fieldName, response.data);
                            } else {
                                this.$emitter.emit('add-flash', { 
                                    type: 'error', 
                                    message: response.data.error || 'Failed to improve text' 
                                });
                            }
                        } catch (error) {
                            console.error('AI Improvement Error:', error);
                            this.$emitter.emit('add-flash', { 
                                type: 'error', 
                                message: 'An error occurred while improving the text' 
                            });
                        } finally {
                            this.aiImproving = false;
                        }
                    },

                    async getAnswer(fieldName) {
                        const field = this.$refs[fieldName] || document.querySelector(`[name="${fieldName}"]`);
                        const prompt = this.aiPrompts && this.aiPrompts[fieldName] ? this.aiPrompts[fieldName].trim() : '';
                        const text = field ? field.value?.trim() : '';

                        if (! prompt) {
                            this.$emitter.emit('add-flash', { 
                                type: 'warning', 
                                message: 'Please enter an AI instruction first' 
                            });

                            return;
                        }

                        this.aiImproving = true;

                        try {
                            const response = await this.$axios.post('{{ route('ai.answer') }}', {
                                prompt: prompt,
                                context: text
                            });

                            if (response.data.success) {
                                // Show answer modal
                                this.showAnswerModal(fieldName, response.data);
                            } else {
                                this.$emitter.emit('add-flash', { 
                                    type: 'error', 
                                    message: response.data.error || 'Failed to get answer' 
                                });
                            }
                        } catch (error) {
                            console.error('AI Answer Error:', error);
                            this.$emitter.emit('add-flash', { 
                                type: 'error', 
                                message: 'An error occurred while getting the answer' 
                            });
                        } finally {
                            this.aiImproving = false;
                        }
                    },

                    showAnswerModal(fieldName, data) {
                        this.aiResults[fieldName] = data;
                        
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                // Apply the answer to the field
                                const field = this.$refs[fieldName] || document.querySelector(`[name="${fieldName}"]`);

                                if (field) {
                                    field.value = data.answer;
                                    
                                    // Trigger input event for Vue reactivity
                                    const event = new Event('input', { bubbles: true });
                                    field.dispatchEvent(event);

                                    this.$emitter.emit('add-flash', { 
                                        type: 'success', 
                                        message: 'Answer applied successfully!' 
                                    });
                                }
                            },
                            message: this.getAnswerHtml(data),
                            agreeText: 'Use This Answer',
                            cancelText: 'Cancel',
                        });
                    },

                    getAnswerHtml(data) {
                        return `
                            <div class="space-y-4">
                                <div class="text-center mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        AI Generated Answer
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Based on: "${this.escapeHtml(data.prompt)}"
                                    </p>
                                </div>

                                <div>
                                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-4 text-sm text-gray-800 dark:text-gray-200 max-h-96 overflow-y-auto">
                                        ${this.escapeHtml(data.answer)}
                                    </div>
                                </div>

                                <div class="text-xs text-gray-500 dark:text-gray-400 text-center pt-2 border-t border-gray-200 dark:border-gray-700">
                                    Model: ${data.model_used} | Tokens: ${data.tokens_used || 'N/A'}
                                </div>
                            </div>
                        `;
                    },

                    showAiComparisonModal(fieldName, data) {
                        this.aiResults[fieldName] = data;
                        
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                // Apply the improved text
                                const field = this.$refs[fieldName] || document.querySelector(`[name="${fieldName}"]`);

                                if (field) {
                                    field.value = data.improved;
                                    
                                    // Trigger input event for Vue reactivity
                                    const event = new Event('input', { bubbles: true });
                                    field.dispatchEvent(event);

                                    this.$emitter.emit('add-flash', { 
                                        type: 'success', 
                                        message: 'Text improved successfully!' 
                                    });
                                }
                            },
                            message: this.getComparisonHtml(data),
                            agreeText: 'Use Improved Text',
                            cancelText: 'Keep Original',
                        });
                    },

                    getComparisonHtml(data) {
                        return `
                            <div class="space-y-4">
                                <div class="text-center mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        AI Text Improvement
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Compare the original and improved versions
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-6a1 1 0 00-1-1H9a1 1 0 00-1 1v6a1 1 0 01-1 1H4a1 1 0 110-2V4z" clip-rule="evenodd" />
                                            </svg>
                                            Original Text
                                        </h4>
                                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md p-3 text-sm text-gray-800 dark:text-gray-200 max-h-48 overflow-y-auto">
                                            ${this.escapeHtml(data.original)}
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            Improved Text
                                        </h4>
                                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-3 text-sm text-gray-800 dark:text-gray-200 max-h-48 overflow-y-auto">
                                            ${this.escapeHtml(data.improved)}
                                        </div>
                                    </div>
                                </div>

                                <div class="text-xs text-gray-500 dark:text-gray-400 text-center pt-2 border-t border-gray-200 dark:border-gray-700">
                                    Model: ${data.model_used} | Tokens: ${data.tokens_used || 'N/A'}
                                </div>
                            </div>
                        `;
                    },

                    escapeHtml(text) {
                        const map = {
                            '&': '&amp;',
                            '<': '&lt;',
                            '>': '&gt;',
                            '"': '&quot;',
                            "'": '&#039;'
                        };
                        return String(text).replace(/[&<>"']/g, m => map[m]);
                    }
                }
            };

            // Apply mixin globally to all Vue components
            if (typeof app !== 'undefined' && app.mixin) {
                app.mixin(window.textareaAiMixin);
            }
        }
    </script>
@endPushOnce
