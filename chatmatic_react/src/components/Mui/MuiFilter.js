import React from 'react';
import { withStyles } from '@material-ui/core/styles';
import Button from '@material-ui/core/Button';
import Menu from '@material-ui/core/Menu';
import MenuItem from '@material-ui/core/MenuItem';
import ListItemIcon from '@material-ui/core/ListItemIcon';
import ListItemText from '@material-ui/core/ListItemText';
import InboxIcon from '@material-ui/icons/MoveToInbox';
import DraftsIcon from '@material-ui/icons/Drafts';
import SendIcon from '@material-ui/icons/Send';
import OutlinedInput from '@material-ui/core/OutlinedInput';
import InputBase from '@material-ui/core/InputBase';
import InputAdornment from '@material-ui/core/InputAdornment';


const StyledMenu = withStyles({
  paper: {
    border: '1px solid #d3d4d5',
  },
})((props) => (
  <Menu
    elevation={0}
    getContentAnchorEl={null}
    anchorOrigin={{
      vertical: 'bottom',
      horizontal: 'center',
    }}
    transformOrigin={{
      vertical: 'top',
      horizontal: 'center',
    }}
    {...props}
  />
));

const StyledMenuItem = withStyles((theme) => ({
  root: {
    '&:focus': {
      backgroundColor: theme.palette.primary.main,
      '& .MuiListItemIcon-root, & .MuiListItemText-primary': {
        color: theme.palette.common.white,
      },
    },
  },
}))(MenuItem);

const ColorButton = withStyles((theme) => ({
    root: {
      color: '#898996',
      background: '#F8F9FB',
      height: '100%',
      borderRight: '1px solid #DCE0E9',
      borderRadius: '0px',
      outline: 'none',
      '&:hover': {
        backgroundColor: '#DCE0E9',
      },
      '&:focus': {
          outline: 'none'
      }
    },
  }))(Button);

export default function CustomizedMenus() {
  const [anchorEl, setAnchorEl] = React.useState(null);
const [value, setValue] = React.useState('');
  const handleClick = (event) => {
    setAnchorEl(event.currentTarget);
  };

  const handleClose = () => {
    setAnchorEl(null);
  };

  const handleChange = (prop) => (event) => {
  };

  const styles = {
    border: '1px solid #DCE0E9',
    height: '48px',
    borderRadius: '10px',
    overflow: 'hidden'
  }
  const inputStyle = {
    padding: '0 1rem'
  }
  return (
    <div className="filter-bar" style={styles}>
      <ColorButton
        aria-controls="customized-menu"
        aria-haspopup="true"
        variant="contained"
        color="primary"
        onClick={handleClick}
        disableElevation
      >
        Search by: All
      </ColorButton>
      <InputBase
        defaultValue=""
        inputProps={{ 'aria-label': 'naked' }}
        endAdornment={<InputAdornment position="end"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.04606 0C3.16097 0 0 3.16097 0 7.04606C0 10.9314 3.16097 14.0921 7.04606 14.0921C10.9314 14.0921 14.0921 10.9314 14.0921 7.04606C14.0921 3.16097 10.9314 0 7.04606 0ZM7.04606 12.7913C3.87816 12.7913 1.30081 10.214 1.30081 7.04609C1.30081 3.87819 3.87816 1.30081 7.04606 1.30081C10.214 1.30081 12.7913 3.87816 12.7913 7.04606C12.7913 10.214 10.214 12.7913 7.04606 12.7913Z" fill="#898996"></path><path d="M15.808 14.8893L12.079 11.1603C11.8249 10.9062 11.4134 10.9062 11.1593 11.1603C10.9052 11.4142 10.9052 11.8261 11.1593 12.08L14.8883 15.809C15.0154 15.936 15.1817 15.9996 15.3482 15.9996C15.5145 15.9996 15.681 15.936 15.808 15.809C16.0621 15.5551 16.0621 15.1432 15.808 14.8893Z" fill="#898996"></path></svg></InputAdornment>}
        aria-describedby="standard-weight-helper-text"
        inputProps={{
            'aria-label': 'weight',
        }}
        style={inputStyle}
        placeHolder="Search Triggers"
      />
      {/* <OutlinedInput
        id="standard-adornment-weight"
        value={value}
        onChange={handleChange('weight')}
        endAdornment={<InputAdornment position="end">Kg</InputAdornment>}
        aria-describedby="standard-weight-helper-text"
        inputProps={{
            'aria-label': 'weight',
        }}
        /> */}
      <StyledMenu
        id="customized-menu"
        anchorEl={anchorEl}
        keepMounted
        open={Boolean(anchorEl)}
        onClose={handleClose}
      >
        <StyledMenuItem>
          <ListItemText primary="All" />
        </StyledMenuItem>
        <StyledMenuItem>
          <ListItemText primary="Trigger name" />
        </StyledMenuItem>
        <StyledMenuItem>
          <ListItemText primary="Widget Type" />
        </StyledMenuItem>
      </StyledMenu>
    </div>
  );
}