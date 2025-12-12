const { useState } = wp.element;
const { BlockControls } = wp.blockEditor;
const { ToolbarGroup, ToolbarButton } = wp.components;
const { __ } = wp.i18n;

export function Controls({ attributes, setAttributes }) {
    const [editMode, setEditMode] = useState(!!attributes._editMode);

    const changeRenderMode = () => {
        const newEditMode = ! editMode;
        setEditMode(newEditMode);
        setAttributes({ _editMode: newEditMode });
    };

    const toolbarLabel = editMode ? __('Preview', 'wordpress-popular-posts') : __('Edit', 'wordpress-popular-posts');
    const toolbarIcon = editMode ? 'format-image' : 'edit';

    return (
        <BlockControls>
            <ToolbarGroup>
                <ToolbarButton
                    label={ toolbarLabel }
                    icon={ toolbarIcon }
                    onClick={ changeRenderMode }
                />
            </ToolbarGroup>
        </BlockControls>
    );
}