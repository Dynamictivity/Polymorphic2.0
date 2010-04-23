# Polymorphic 2.0

## Introduction
Polymorphic that works with deep associations and containable. You can also have specific conditions for each type of associated model. Improvement of the original Polymorphic by Andy Dawson (AD7Six)

Version 2.0 by Gothfunc and Theaxiom

## Usage
In your find from the model that has polymorphic attached (i.e. Notification), here is a sample conditions array:

	array(
		'conditions' => array(
			'Notification.user_id' => 1
		),
		'polyConditions' => array(
			'Post' => array(
				'conditions' => array(
					'Post.user_id' => 1
				),
				'contain' => array(
					'User' => array(
						'fields' => array('id', 'name')
					)
				)
			),
			'Comment' => array(
				'conditions' => array(
					'Comment.is_active' => true
				)
			)
		)
	)	