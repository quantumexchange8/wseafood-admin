<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import Placeholder from '@tiptap/extension-placeholder'
import TextAlign from '@tiptap/extension-text-align'
import {
    IconBold,
    IconItalic,
    IconUnderline,
    IconList,
    IconListNumbers,
    IconArrowBackUp,
    IconArrowForwardUp,
    IconAlignLeft,
    IconAlignCenter,
    IconAlignRight,
    IconAlignJustified,
    IconLetterPSmall,
    IconH1,
    IconH2,
} from "@tabler/icons-vue";
import { computed, watch } from 'vue';
import { trans } from 'laravel-vue-i18n'

const props = defineProps({
    modelValue: String,
    invalid: String,
})

const editorClass = computed(() =>
    [
        'rounded-bl-lg rounded-br-lg dark:bg-surface-950 border hover:border-surface-400 dark:hover:border-surface-600 focus:border-primary focus:outline-none focus:ring-0 focus:border-primary py-2.5 px-3 h-80 overflow-y-auto dark:text-white prose dark:prose-invert min-w-full text-sm',
        props.invalid ? 'border-red-500 dark:border-red-400 focus:border-red-500 dark:focus:border-red-400' : 'border-transparent'
    ].join(' ')
);

watch(() => props.invalid, (val) => {
    if (editor.value) {
        editor.value.setOptions({
            editorProps: {
                attributes: {
                    class: editorClass.value,
                },
            },
        });
    }
});

const emit = defineEmits(['update:modelValue'])

const editor = useEditor({
    content: props.modelValue,
    onUpdate: ({editor}) => {
        emit('update:modelValue', editor.getHTML())
    },
    editorProps: {
        attributes: {
            class: editorClass.value,
        },
    },
    extensions: [
        StarterKit,
        Underline,
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        Placeholder.configure({
            emptyEditorClass: 'cursor-text before:content-[attr(data-placeholder)] before:absolute before:top-[10px] before:left-3 before:text-mauve-11 before:opacity-50 before-pointer-events-none text-sm',
            placeholder: trans('public.enter_content'),
        }),
    ],
})

</script>

<template>
    <section
        v-if="editor"
        class="flex items-center flex-wrap gap-5 dark:bg-surface-950 p-1 w-full border-b border-surface-200 dark:border-surface-600"
    >
        <div class="flex items-center flex-wrap gap-1">
            <button
                type="button"
                @click="editor.chain().focus().toggleBold().run()"
                :disabled="!editor.can().chain().focus().toggleBold().run()"
                :class="[
                'rounded p-1 dark:text-white',
                { 'bg-surface-200 dark:bg-surface-700': editor.isActive('bold') }
            ]"
            >
                <IconBold size="20" />
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleItalic().run()"
                :disabled="!editor.can().chain().focus().toggleItalic().run()"
                :class="[
                'rounded p-1 dark:text-white',
                { 'bg-surface-200 dark:bg-surface-700': editor.isActive('italic') }
            ]"
            >
                <IconItalic size="20" />
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleUnderline().run()"
                :class="[
                'rounded p-1 dark:text-white',
                { 'bg-surface-200 dark:bg-surface-700': editor.isActive('underline') }
            ]"
            >
                <IconUnderline size="20" />
            </button>
        </div>

        <div class="flex items-center flex-wrap gap-1">
            <button
                type="button"
                @click="editor.chain().focus().setParagraph().run()"
                :class="[
                'rounded p-1 dark:text-white',
                { 'bg-surface-200 dark:bg-surface-700': editor.isActive('paragraph') }
            ]"
            >
                <IconLetterPSmall size="20" />
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                :class="[
                'rounded p-1 dark:text-white',
                { 'bg-surface-200 dark:bg-surface-700': editor.isActive('heading', { level: 1 }) }
            ]"
            >
                <IconH1 size="20" />
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="[
                'rounded p-1 dark:text-white',
                { 'bg-surface-200 dark:bg-surface-700': editor.isActive('heading', { level: 2 }) }
            ]"
            >
                <IconH2 size="20" />
            </button>
        </div>

        <div class="flex items-center flex-wrap gap-1">
            <button
                type="button"
                @click="editor.chain().focus().toggleBulletList().run()"
                :class="[
                'rounded p-1 dark:text-white',
                { 'bg-surface-200 dark:bg-surface-700': editor.isActive('bulletList') }
            ]"
            >
                <IconList size="20" />
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleOrderedList().run()"
                :class="[
                'rounded p-1 dark:text-white',
                { 'bg-surface-200 dark:bg-surface-700': editor.isActive('orderedList') }
            ]"
            >
                <IconListNumbers size="20" />
            </button>
        </div>

        <div class="flex items-center flex-wrap gap-1">
            <button
                type="button"
                @click="editor.chain().focus().setTextAlign('left').run()"
                :class="[
                'rounded p-1 dark:text-white',
                { 'bg-surface-200 dark:bg-surface-700': editor.isActive({ textAlign: 'left' }) }
            ]"
            >
                <IconAlignLeft size="20" />
            </button>
            <button
                type="button"
                @click="editor.chain().focus().setTextAlign('center').run()"
                :class="[
                'rounded p-1 dark:text-white',
                { 'bg-surface-200 dark:bg-surface-700': editor.isActive({ textAlign: 'center' }) }
            ]"
            >
                <IconAlignCenter size="20" />
            </button>
            <button
                type="button"
                @click="editor.chain().focus().setTextAlign('right').run()"
                :class="[
                'rounded p-1 dark:text-white',
                { 'bg-surface-200 dark:bg-surface-700': editor.isActive({ textAlign: 'right' }) }
            ]"
            >
                <IconAlignRight size="20" />
            </button>
            <button
                type="button"
                @click="editor.chain().focus().setTextAlign('justify').run()"
                :class="[
                'rounded p-1 dark:text-white',
                { 'bg-surface-200 dark:bg-surface-700': editor.isActive({ textAlign: 'justify' }) }
            ]"
            >
                <IconAlignJustified size="20" />
            </button>
        </div>

        <div class="flex items-center flex-wrap gap-1">
            <button
                type="button"
                class="dark:text-white disabled:text-surface-400 dark:disabled:text-surface-600 rounded p-1"
                @click="editor.chain().focus().undo().run()"
                :disabled="!editor.can().chain().focus().undo().run()">
                <IconArrowBackUp size="20" />
            </button>
            <button
                type="button"
                class="dark:text-white disabled:text-surface-400 dark:disabled:text-surface-600 rounded p-1"
                @click="editor.chain().focus().redo().run()"
                :disabled="!editor.can().chain().focus().redo().run()">
                <IconArrowForwardUp size="20" />
            </button>
        </div>
    </section>
    <EditorContent :editor="editor" class="w-full" />
</template>
