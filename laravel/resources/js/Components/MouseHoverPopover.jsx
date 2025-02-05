import * as React from 'react';
import Popover from '@mui/material/Popover';
import Typography from '@mui/material/Typography';

export default function MouseHoverPopover({ data }) {
	const [anchorEl, setAnchorEl] = React.useState(null);

	const handlePopoverOpen = (event) => {
		setAnchorEl(event.currentTarget);
	};

	const handlePopoverClose = () => {
		setAnchorEl(null);
	};

	const open = Boolean(anchorEl);

	return (
		<div
			aria-owns={open ? 'mouse-over-popover' : undefined}
			aria-haspopup="true"
			onMouseEnter={handlePopoverOpen}
			onMouseLeave={handlePopoverClose}
		>
			{data}
			<Popover
				id="mouse-over-popover"
				sx={{ pointerEvents: 'none' }}
				open={open}
				anchorEl={anchorEl}
				anchorOrigin={{
					vertical: 'bottom',
					horizontal: 'left',
				}}
				transformOrigin={{
					vertical: 'top',
					horizontal: 'left',
				}}
				onClose={handlePopoverClose}
				disableRestoreFocus
			>
					<Typography sx={{ p: 1, maxWidth: '400px' }}>{data}</Typography>
	
			</Popover>
		</div>
	);
}
