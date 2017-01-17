<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class NF_MergeTags_Fields
 */
final class NF_MergeTags_Fields extends NF_Abstracts_MergeTags
{
    protected $id = 'fields';

    public function __construct()
    {
        parent::__construct();
        $this->title = __( 'Fields', 'ninja-forms' );
        $this->merge_tags = Ninja_Forms()->config( 'MergeTagsFields' );
    }

    public function __call($name, $arguments)
    {
        return $this->merge_tags[ $name ][ 'field_value' ];
    }

    public function all_fields()
    {
        $return = '<table>';
        $hidden_field_types = array( 'html' );
        foreach( $this->merge_tags[ 'all_fields' ][ 'fields' ] as $field ){

            if( in_array( $field[ 'type' ], array_values( $hidden_field_types ) ) ) continue;

            $field[ 'value' ] = apply_filters( 'ninja_forms_merge_tag_value_' . $field[ 'type' ], $field[ 'value' ], $field );

            if( is_array( $field[ 'value' ] ) ) $field[ 'value' ] = implode( ', ', $field[ 'value' ] );

            $return .= '<tr><td>' . $field[ 'label' ] .':</td><td>' . $field[ 'value' ] . '</td></tr>';
        }
        $return .= '</table>';
        return $return;
    }

    public function all_field_plain()
    {
        $return = '';
        foreach( $this->merge_tags[ 'all_fields' ][ 'fields' ] as $field ){
            $field[ 'value' ] = apply_filters( 'ninja_forms_merge_tag_value_' . $field[ 'type' ], $field[ 'value' ], $field );

            if( is_array( $field[ 'value' ] ) ) $field[ 'value' ] = implode( ', ', $field[ 'value' ] );

            $return .= $field[ 'label' ] .': ' . $field[ 'value' ] . "\r\n";
        }
        return $return;
    }

    public function add_field( $field )
    {
        $hidden_field_types = apply_filters( 'nf_sub_hidden_field_types', array() );
        if( in_array( $field[ 'type' ], $hidden_field_types ) ) return;

        $callback = 'field_' . $field[ 'id' ];

        $this->merge_tags[ 'all_fields' ][ 'fields' ][ $callback ] = $field;

        if( is_array( $field[ 'value' ] ) ) $field[ 'value' ] = implode( ',', $field[ 'value' ] );

	    $value = apply_filters('ninja_forms_merge_tag_value_' . $field['type'], $field['value'], $field);

	    $this->add( $callback, $field['id'], '{field:' . $field['id'] . '}', $value );

        if( isset( $field[ 'key' ] ) ) {
            $callback = 'field_' . $field['key'];
            $this->add( $callback, $field['key'], '{field:' . $field['key'] . '}', $value );
        }

        $callback = 'field_' . $field[ 'key' ] . '_calc';
        $calc_value = apply_filters( 'ninja_forms_merge_tag_calc_value_' . $field[ 'type' ], $field['value'], $field );
        $this->add( $callback, $field['key'], '{field:' . $field['key'] . ':calc}', $calc_value );
    }

	public function add( $callback, $id, $tag, $value )
	{
		$this->merge_tags[ $callback ] = array(
			'id'          => $id,
			'tag'         => $tag,
			'callback'    => $callback,
			'field_value' => $value,
		);
	}

} // END CLASS NF_MergeTags_Fields
