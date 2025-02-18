import React, { useState } from 'react'
import { Button, Modal, Typography } from '@mui/material';

export default function OccurrenceModal({ occurrences = [], link }) {

	const [open, setOpen] = useState(false);
	const handleOpen = () => setOpen(true);
	const handleClose = () => setOpen(false);

	const occurInfo = Array.isArray(occurrences) ? occurrences.map((occur) => ({
		type: occur.type,
		description: occur.description
	})) : [];

	return (
		<div className='justify-center'>
			<Button
				variant='outlined'
				onClick={handleOpen}
				sx={{
					maxHeight: '30px',
					minHeight: '30px',
					margin: '0px 4px'
				}}
			>
				{occurrences.length} Ocorrência(s)
			</Button>
			<Modal
				open={open}
				onClose={handleClose}
				style={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}
			>

				<div style={{ top: '50%', margin: 'auto', backgroundColor: 'white', padding: '20px', borderRadius: "8px" }}>
					{occurInfo.map((elem, index) => (
						<Typography key={index}>{elem.type} - {elem.description}</Typography>
					))}
					<Button href={route('orders.occurrences', link)}>Mais informações</Button>
				</div>
			</Modal>
		</div>
	)
}
